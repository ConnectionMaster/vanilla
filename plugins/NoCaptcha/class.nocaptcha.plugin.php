<?php

if (!defined('APPLICATION')) exit();

$PluginInfo['NoCaptcha'] = array(
    'Name' => 'NoCaptcha',
    'Description' => 'Use the new No Captcha ReCaptcha',
    'Version' => '0.1',
    'Author' => 'Benjam Welker',
    'AuthorEmail' => 'bwelker@daz3d.com',
    'SettingsUrl' => '/settings/nocaptcha',
    'SettingsPermission' => 'Garden.Settings.Manage',
    'MobileFriendly' => TRUE,
);

class NoCaptcha extends Gdn_Plugin {

    public function Setup() {
        if ( ! function_exists('curl_init') || ! function_exists('curl_setopt') || ! function_exists('curl_exec')) {
            throw new Exception(T('Curl required'));
        }
    }

    public function SettingsController_Nocaptcha_Create($Sender) {
        $Sender->Permission('Garden.Settings.Manage');

        $Conf = new ConfigurationModule($Sender);
        $Conf->Initialize(array(
            'Plugins.NoCaptcha.SiteKey' => array('Control' => 'TextBox', 'LabelCode' => 'Site Key', 'Options' => array('class' => 'InputBox WideInput')),
            'Plugins.NoCaptcha.SecretKey' => array('Control' => 'TextBox', 'LabelCode' => 'Secret Key', 'Options' => array('class' => 'InputBox WideInput')),
            'Plugins.NoCaptcha.OnComments' => array('Control' => 'CheckBox', 'LabelCode' => 'Use on comments (still a bit buggy)'),
        ));

        $Sender->AddSideMenu();
        $Sender->SetData('Title', sprintf(T('%s Settings'), T('reCAPTCHA')));
        $Sender->ConfigurationModule = $Conf;
        $Conf->RenderAll();
    }

    // === DISCUSSIONS =======================

    // new discussion header
    public function PostController_Render_Before($Sender) {
        $Sender->Head->AddScript('https://www.google.com/recaptcha/api.js');
    }

    // new discussion entry
    public function PostController_BeforeFormButtons_Handler( ) {
        echo self::NocaptchaHtml( );
    }

    // new discussion save
    public function DiscussionModel_BeforeSaveDiscussion_Handler($Sender) {
        try {
            if ( ! self::ValidateCaptcha( )) {
                $Sender->Validation->AddValidationResult('reCAPTCHA', 'You must fill out the reCAPTCHA form so we know you are a human');
//                $Sender->EventArguments['FormPostValues'] = array( );
                return false;
            }
        }
        catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    // === COMMENTS ==========================

    // new comment header
    public function DiscussionController_Render_Before($Sender) {
        if (C('Plugins.NoCaptcha.OnComments')) {
            $Sender->Head->AddScript('https://www.google.com/recaptcha/api.js');
        }
    }

    // new comment entry
    public function DiscussionController_BeforeFormButtons_Handler( ) {
        if (C('Plugins.NoCaptcha.OnComments')) {
            echo self::NocaptchaHtml();
        }
    }

    // new comment save
    public function CommentModel_BeforeSaveComment_Handler($Sender) {
        if (C('Plugins.NoCaptcha.OnComments')) {
            try {
                if ( ! self::ValidateCaptcha()) {
                    $Sender->Validation->AddValidationResult('reCAPTCHA', 'You must fill out the reCAPTCHA form so we know you are a human');
//                $Sender->EventArguments['FormPostValues'] = array( );
                    return false;
                }
            }
            catch (Exception $e) {
                throw $e;
            }
        }

        return true;
    }

    public static function ValidateCaptcha( ) {
        $Response = ArrayValue('g-recaptcha-response', $_POST, '');

        if ( ! $Response)
            return false;

        $Url = 'https://www.google.com/recaptcha/api/siteverify';
        $Data = array(
            'secret' => C('Plugins.NoCaptcha.SecretKey'),
            'response' => $Response,
            'remoteip' => $_SERVER['REMOTE_ADDR'],
        );

        $Handler = curl_init();
        curl_setopt($Handler, CURLOPT_URL, $Url);
        curl_setopt($Handler, CURLOPT_PORT, '443');
        curl_setopt($Handler, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($Handler, CURLOPT_HEADER, FALSE);
        curl_setopt($Handler, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
        curl_setopt($Handler, CURLOPT_USERAGENT, ArrayValue('HTTP_USER_AGENT', $_SERVER, 'NoCaptcha Vanilla'));
        curl_setopt($Handler, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($Handler, CURLOPT_POST, TRUE);
        curl_setopt($Handler, CURLOPT_POSTFIELDS, http_build_query($Data));

        $Response = curl_exec($Handler);

        if ($Response) {
            $Result = json_decode($Response, true);
            $ErrorCodes = GetValue('error_codes', $Result);
            if ($Result && GetValue('success', $Result)) {
                return true;
            }
            else if ( ! empty($ErrorCodes) && $ErrorCodes != array('invalid-input-response')) {
                throw new Exception(FormatString(T('Could not get check if human! Error codes: {ErrorCodes}'), array('ErrorCodes' => join(', ', $ErrorCodes))));
            }
        }
        else {
            throw new Exception(T('Could not get check if human!'));
        }

        return false;
    }

    public static function NocaptchaHtml($SiteKey = '') {
        if (empty($SiteKey)) {
            $SiteKey = C('Plugins.NoCaptcha.SiteKey');
        }

        $Attributes = C('Plugins.NoCaptcha.Attributes', array());
        $Attributes = array_merge($Attributes, array('class' => 'g-recaptcha', 'data-sitekey' => $SiteKey));

        $Plugin = Gdn::PluginManager()->GetPluginInstance('NoCaptcha');
        if ($Plugin) {
            $Plugin->EventArguments['Attributes'] = &$Attributes;
            $Plugin->FireEvent('BeforeDivReturn');
        }

        return '<div ' . Attribute($Attributes) . '></div>';
    }

}
