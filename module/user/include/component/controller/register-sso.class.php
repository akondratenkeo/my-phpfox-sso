<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 *
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond Benc
 * @package  		Module_User
 * @version 		$Id: register.class.php 7153 2014-02-24 16:10:37Z Fern $
 */
class User_Component_Controller_Register_Sso extends Phpfox_Component
{
	/**
	 * Class process method wnich is used to execute this component.
	 */
	public function process()
	{

        /*
         * TODO: Add column in DB for mark SSO users
         */
        if (!Phpfox::getParam('user.allow_user_registration'))
		{
			$this->url()->send('');	
		}

		define('PHPFOX_DONT_SAVE_PAGE', true);
		
		if (Phpfox::isUser())
		{
			$this->url()->send('profile');
		}

		$oValid = Phpfox::getLib('validator')->set(array('sFormName' => 'js_form', 'aParams' => Phpfox::getService('user.register')->getSsoValidation()));

		if ($aVals = $this->request()->getArray('val'))
		{
            Phpfox::getService('user.validate')->email($aVals['email']);

            if (!Phpfox::getLib('mail')->checkEmail($aVals['email']))
            {
                return Phpfox_Error::set(Phpfox::getPhrase('user.email_is_not_valid'));
            }

            $aCustom = Phpfox::getLib('request')->getArray('custom');

            $aCustomFields = Phpfox::getService('custom')->getForEdit(array('user_main', 'user_panel', 'profile_panel'), null, null, true);
            foreach ($aCustomFields as $aCustomField) {
                if ($aCustomField['on_signup'] && $aCustomField['is_required'] && empty($aCustom[$aCustomField['field_id']]))
                {
                    Phpfox_Error::set(Phpfox::getPhrase('user.the_field_field_is_required', array('field' => Phpfox::getPhrase($aCustomField['phrase_var_name']))));
                }
            }

            if (Phpfox::getParam('user.force_user_to_upload_on_sign_up')) {

                if (!isset($_FILES['image']['name']) || empty($_FILES['image']['name']) ) {
                    Phpfox_Error::set(Phpfox::getPhrase('photo.please_upload_an_image_for_your_profile'));
                }
            }

            if ($oValid->isValid($aVals)) {

                $aVals['password'] = '40649657523bf7b891fd8bd3f0c7dc49';
                $aVals['isSso'] = true;

                if ($iId = Phpfox::getService('user.process')->add($aVals)) {

                    if (Phpfox::getService('user.auth')->login($aVals['email'], $aVals['password'])) {

                        if (is_array($iId)) {
                            $this->url()->forward($iId[0]);
                        } else {
                            $sRedirect = Phpfox::getParam('user.redirect_after_signup');

                            if (!empty($sRedirect)) {
                                $this->url()->send($sRedirect);
                            } else {
                                $this->url()->send('');
                            }
                        }
                    }
                } else {
                    if (Phpfox::getParam('user.multi_step_registration_form')) {
                        $this->template()->assign('bIsPosted', true);
                    }
                }
            } else {
                $this->template()->assign(array(
                        'iTimeZonePosted' => (isset($aVals['time_zone']) ? $aVals['time_zone'] : 0)
                    )
                );

                $this->template()->assign('bIsPosted', true);

                $this->setParam(array(
                        'country_child_value' => (isset($aVals['country_iso']) ? $aVals['country_iso'] : 0),
                        'country_child_id' => (isset($aVals['country_child_id']) ? $aVals['country_child_id'] : 0)
                    )
                );
            }
		} else {
            if (!Phpfox::getLib('request')->get('req3')){
                $this->url()->send('');
            }

            $ssoSalt = 'WeWMyOXhVBhQzV2';
            $ssoData_encode = base64_decode(Phpfox::getLib('request')->get('req3'));
            if ($saltPos = strpos($ssoData_encode, $ssoSalt)) {
                $ssoData = explode(';', base64_decode(substr($ssoData_encode, 0, $saltPos)));

                $this->template()->assign('aForms', array(
                    'email' => $ssoData[0],
                    'first_name' => $ssoData[1],
                    'last_name' => $ssoData[2]
                ));

            } else {
                $this->url()->send('');
            }
		}

		$sTitle = Phpfox::getPhrase('user.sign_and_start_using_site', array('site' => Phpfox::getParam('core.site_title')));

        (($sPlugin = Phpfox_Plugin::get('user.component_controller_register_8')) ? eval($sPlugin) : false);
		$aSettings = Phpfox::getService('custom')->getForEdit(array('user_main', 'user_panel', 'profile_panel'), null, null, true);
        /*if ($_SERVER['REMOTE_ADDR'] == '195.24.142.141') {
            if (!isset($aCustom) || $aCustom == null) {
                foreach($aSettings as $aSetting) {
                    foreach($aSetting['options'] as $aOptions) {
                        if (isset($aOptions['selected'])) {
                            $aOptions['selected'] = false;
                        }
                    }
                }
            }
        }*/

		$this->template()->setTitle($sTitle)
			->setFullSite()
			->setPhrase(array(
					'user.continue'
				)
			)
			->setHeader('cache', array(
					'register.css' => 'module_user',
					'register.js' => 'module_user',					
					'country.js' => 'module_core'
				)
			)
			->assign(array(
				'sCreateJs' => $oValid->createJS(),
				'sGetJsForm' => $oValid->getJsForm(),
				'sSiteUrl' => Phpfox::getParam('core.path'),
				'aTimeZones' => Phpfox::getService('core')->getTimeZones(),
				'aPackages' => (Phpfox::isModule('subscribe') ? Phpfox::getService('subscribe')->getPackages(true) : null),
				'aSettings' => $aSettings,
				'sDobStart' => Phpfox::getParam('user.date_of_birth_start'),
				'sDobEnd' => Phpfox::getParam('user.date_of_birth_end'),
				'sJanrainUrl' => (Phpfox::isModule('janrain') ? Phpfox::getService('janrain')->getUrl() : ''),
				'sUserEmailCookie' => Phpfox::getCookie('invited_by_email_form'),
				'sSiteTitle' => Phpfox::getParam('core.site_title')
			)
		);
	}

	/**
	 * Garbage collector. Is executed after this class has completed
	 * its job and the template has also been displayed.
	 */
	public function clean()
	{
		(($sPlugin = Phpfox_Plugin::get('user.component_controller_register_clean')) ? eval($sPlugin) : false);
	}
}

?>
