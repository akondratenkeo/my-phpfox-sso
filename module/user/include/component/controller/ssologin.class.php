<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 * User SSO Login
 *
 * @author			dev
 * @package  		Module_User
 * @version 		$Id: ssologin.class.php 5463 2015-11-18 $
 */
class User_Component_Controller_Ssologin extends Phpfox_Component {
    /**
     * Class process method which is used to execute this component.
     */
    public function process() {
        $ssoSalt = 'WeWMyOXhVBhQzV2';
        if (Phpfox::getLib('session')->get('is_test_sso')){
            $auth = new SimpleSAML_Auth_Simple('baylortest');
        } else {
            $auth = new SimpleSAML_Auth_Simple('baylorsso');
        }
        $isAuthenticated = $auth->isAuthenticated();
        $attributes = $auth->getAttributes();

        /**
         * if user not auth
         * else if user was auth
         */
        if (!$isAuthenticated){
            if(Phpfox::getLib('request')->get('req4') == 'login'){
                $auth->requireAuth(array(
                    'ReturnTo' => 'https://baylor.g9md.net/user/ssologin',
                    'KeepPost' => FALSE,
                ));
            }
        } else {
            if(Phpfox::getLib('request')->get('req4') == 'logout'){
                Phpfox::getService('user.auth')->logout();
                $auth->logout('https://baylor.g9md.net/');
            } else {
                $aVals = array();
                if($isAuthenticated && !empty($attributes) && is_array($attributes)){
                    $_attrs = array();
                    foreach($attributes as $key => $value){
                        $_attrs[] = $value[0];
                    }
                    $_email = $_attrs[0];

                    $sSelect = 'user_id, email, user_name, password, password_salt, status_id';
                    $aRows = reset(Phpfox::getLib('database')->select($sSelect)
                        ->from(Phpfox::getT('user'), 'u')
                        ->where("email = '" . $_email . "'")
                        ->execute('getRows'));

                    if ($aRows === false) {
                        $ssoData_encode = base64_encode(base64_encode(implode(';', $_attrs)).$ssoSalt);
                        $this->url()->send('user.register-sso', array($ssoData_encode));
                    }

                    $aVals = array(
                        'login' => $_email,
                        'remember_me' => true,
                        'password' => $aRows['password']
                    );
                }
                if(!empty($aVals) && is_array($aVals)) {
                    list($bLogged, $aUser) = (Phpfox::getService('user.auth')->login($aVals['login'], $aVals['password'], (isset($aVals['remember_me']) ? true : false), Phpfox::getParam('user.login_type')));
                    if ($bLogged) {
                        $sReturn = '';
                        if (Phpfox::getParam('core.redirect_guest_on_same_page'))
                        {
                            $sReturn = Phpfox::getLib('session')->get('redirect');
                            if (is_bool($sReturn))
                            {
                                $sReturn = '';
                            }

                            if ($sReturn)
                            {
                                $aParts = explode('/', trim($sReturn, '/'));
                                if (isset($aParts[0]))
                                {
                                    $aParts[0] = Phpfox::getLib('url')->reverseRewrite($aParts[0]);
                                }
                                if (isset($aParts[0]) && !Phpfox::isModule($aParts[0]))
                                {
                                    $aUserCheck = Phpfox::getService('user')->getByUserName($aParts[0]);
                                    if (isset($aUserCheck['user_id']))
                                    {
                                        if (isset($aParts[1]) && !Phpfox::isModule($aParts[1]))
                                        {
                                            $sReturn = '';
                                        }
                                    }
                                    else
                                    {
                                        $sReturn = '';
                                    }
                                }
                            }
                        }

                        if (!$sReturn)
                        {
                            $sReturn = Phpfox::getParam('user.redirect_after_login');
                        }

                        if ($sReturn == 'profile')
                        {
                            $sReturn = $aUser['user_name'];
                        }

                        Phpfox::getLib('session')->remove('redirect');

                        if (preg_match('/^(http|https):\/\/(.*)$/i', $sReturn))
                        {
                            $this->url()->forward($sReturn);
                        }

                        $sReturn = trim($sReturn, '/');
                        $sReturn = str_replace('/', '.', $sReturn);

                        Phpfox::getLib('session')->remove('redirect');

                        if (isset($aUser['status_id']) && $aUser['status_id'] == 1)
                        {
                            $this->url()->send($sReturn, null, Phpfox::getPhrase('user.you_still_need_to_verify_your_email_address'));
                        }

                        if (Phpfox::getParam('user.verify_email_at_signup'))
                        {
                            $bDoRedirect = Phpfox::getLib('session')->get('verified_do_redirect');
                            Phpfox::getLib('session')->remove('verified_do_redirect');
                            if ( (int)$bDoRedirect == 1 && Phpfox::getParam('user.redirect_after_signup') != '')
                            {
                                $sReturn = Phpfox::getParam('user.redirect_after_signup');
                            }
                        }
                        $this->url()->send($sReturn);
                    } else {
                        if ($sPlugin = Phpfox_Plugin::get('user.controller_login_login_failed')){eval($sPlugin);}
                    }
                }
            }
        }
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('user.component_controller_ssologin_clean')) ? eval($sPlugin) : false);
    }
}

?>