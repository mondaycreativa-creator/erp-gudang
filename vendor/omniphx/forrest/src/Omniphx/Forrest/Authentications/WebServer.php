<?php

namespace Omniphx\Forrest\Authentications;

use GuzzleHttp\ClientInterface;
use Omniphx\Forrest\Client;
use Omniphx\Forrest\Exceptions\MissingKeyException;
use Omniphx\Forrest\Interfaces\EventInterface;
use Omniphx\Forrest\Interfaces\EncryptorInterface;
use Omniphx\Forrest\Interfaces\InputInterface;
use Omniphx\Forrest\Interfaces\RedirectInterface;
use Omniphx\Forrest\Interfaces\StorageInterface;
use Omniphx\Forrest\Interfaces\WebServerInterface;

class WebServer extends Client implements WebServerInterface
{
    /**
     * Call this method to redirect user to login page and initiate
     * the Web Server OAuth Authentication Flow.
     *
     * @param null $loginURL
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function base64_urlencode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function authenticate($url = null, $stateOptions = [])
    {
        $company_settings        = getCompanyAllSetting();
        $consumerKey = !empty($company_settings['salesforce_consumer_key']) ? $company_settings['salesforce_consumer_key'] :'';

        $codeVerifier = bin2hex(random_bytes(32));

        session(['code_verifier' => $codeVerifier]);
        // Calculate the code challenge
        $codeChallenge = $this->base64_urlencode(hash('sha256', $codeVerifier, true));

        // $loginURL  = $url === null ? $this->credentials['loginURL'] : $url;
        $loginURL  = 'https://login.salesforce.com';

        $stateOptions['loginUrl'] = $loginURL;
        $state = '&state='.urlencode(json_encode($stateOptions));
        $parameters = $this->settings['parameters'];

        $loginURL .= '/services/oauth2/authorize';
        $loginURL .= '?response_type=code';
        $loginURL .= '&client_id='.$consumerKey;
        $loginURL .= '&redirect_uri='.urlencode(url('salesforce/oauth'));
        $loginURL .= !empty($parameters['display']) ? '&display='.$parameters['display'] : '';
        $loginURL .= $parameters['immediate'] ? '&immediate=true' : '';
        $loginURL .= !empty($parameters['scope']) ? '&scope='.rawurlencode($parameters['scope']) : '';
        $loginURL .= !empty($parameters['prompt']) ? '&prompt='.rawurlencode($parameters['prompt']) : '';
        $loginURL .= $state;
        $loginURL .= '&code_challenge=' . $codeChallenge;
        $loginURL .= '&code_challenge_method=S256';

        return $this->redirect->to($loginURL);
    }

    /**
     * When settings up your callback route, you will need to call this method to
     * acquire an authorization token. This token will be used for the API requests.
     *
     * @return RedirectInterface
     */
    // public function credFromDB($response)
    // {
    //     $response = (array) json_decode($response);
    //     //Encrypt token and store token in storage.
    //     $this->tokenRepo->put(($response));
        
    //     if (isset($response->refresh_token)) {
    //         $this->refreshTokenRepo->put($response->refresh_token);
    //     }
    //     $this->storeVersion();
    //     $this->storeResources();
    // }

    public function callback()
    {
        $company_settings        = getCompanyAllSetting();

        // Retrieve the stored code verifier from the session
        $codeVerifier = session('code_verifier');

        //Salesforce sends us an authorization code as part of the Web Server OAuth Authentication Flow
        $code = $this->input->get('code');

        $stateOptions = json_decode(urldecode($this->input->get('state')), true);

        //Store instance URL
        $loginURL = $stateOptions['loginUrl'];

        // Store user options so they can be used later
        // $this->stateRepo->put($stateOptions);

        $tokenURL = $loginURL.'/services/oauth2/token';

        $jsonResponse = $this->httpClient->request('post', $tokenURL, [
            'form_params' => [
                'code'          => $code,
                'grant_type'    => 'authorization_code',
                'client_id'     => !empty($company_settings['salesforce_consumer_key']) ? $company_settings['salesforce_consumer_key'] :'',
                'client_secret' => !empty($company_settings['salesforce_consumer_secret']) ? $company_settings['salesforce_consumer_secret'] :'',
                'redirect_uri'  => url('salesforce/oauth'),
                'code_verifier' => $codeVerifier,
            ],
        ]);

        // Response returns an json of access_token, instance_url, id, issued_at, and signature.
        $response = json_decode($jsonResponse->getBody(), true);
        $this->handleAuthenticationErrors($response);
        $post['salesforce_token']      = json_encode($response);

        if(isset($response['refresh_token']))
        {
            $post['salesforce_refresh_token']      = $response['refresh_token'];
        }

        $check = \Modules\SalesForce\Entities\SalesForceUtility::saveSettings($post);

        //Encrypt token and store token in storage.
        $this->tokenRepo->put($response);

        if (isset($response['refresh_token'])) {
            $this->refreshTokenRepo->put($response['refresh_token']);
        }

        $this->storeVersion();
        $this->storeResources();

        return $stateOptions;
    }

    /**
     * Refresh authentication token.
     *
     * @return void
     */
    public function refresh()
    {
        $company_settings        = getCompanyAllSetting();

        // $refreshToken = $this->refreshTokenRepo->get();
        $refreshToken = company_setting('salesforce_refresh_token');
        $tokenURL = $this->getLoginURL();
        $tokenURL .= '/services/oauth2/token';


        $response = $this->httpClient->request('post', $tokenURL, [
            'form_params'    => [
                'refresh_token' => $refreshToken,
                'grant_type'    => 'refresh_token',
                'client_id'     => !empty($company_settings['salesforce_consumer_key']) ? $company_settings['salesforce_consumer_key'] :'',
                'client_secret' => !empty($company_settings['salesforce_consumer_secret']) ? $company_settings['salesforce_consumer_secret'] :'',
            ],
        ]);

        // Response returns an json of access_token, instance_url, id, issued_at, and signature.
        $token = json_decode($response->getBody(), true);
        // Encrypt token and store token and in storage.
        $post['salesforce_token']      = json_encode($token);

        $check = \Modules\SalesForce\Entities\SalesForceUtility::saveSettings($post);
        // $this->tokenRepo->put($token);
    }

    /**
     * Revokes access token from Salesforce. Will not flush token from storage.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function revoke()
    {
        // $accessToken = $this->tokenRepo->get()['access_token'];
        $accessToken = company_setting('access_token');
        $url = $this->getLoginURL();
        $url .= '/services/oauth2/revoke';

        $options['headers']['content-type'] = 'application/x-www-form-urlencoded';
        $options['form_params']['token'] = $accessToken;

        return $this->httpClient->post($url, $options);
    }

    /**
     * Retrieve login URL.
     *
     * @return string
     */
    private function getLoginURL()
    {
        try {
            return $this->instanceURLRepo->get();
        } catch (MissingKeyException $e) {
            return $loginURL = $this->credentials['loginURL'];
        }
    }
}
