<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 * 
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond_Benc
 * @package 		Phpfox_Component
 * @version 		$Id: login-header.class.php 6607 2013-09-10 09:00:38Z Miguel_Espinoza $
 */
class User_Component_Block_Login_Header extends Phpfox_Component
{
	/**
	 * Class process method wnich is used to execute this component.
	 */
	public function process()
	{
		if ($sPlugin = Phpfox_Plugin::get('user.component_block_login_header')){eval($sPlugin);if (isset($mReturnFromPlugin)){return $mReturnFromPlugin;}}

        $showSsoButton = true;
		$is_test_sso = false;
		$link = '/user/ssologin/sso/login';
		if(Phpfox::getLib('session')->set('is_test_sso')){
			Phpfox::getLib('session')->remove('is_test_sso');
		}
		if(Phpfox::getLib('request')->get('istestsso')) {
			$is_test_sso = true;
			$_SESSION['is_test_sso'] = true;
			$link = '/user/ssotest/sso/login';
			Phpfox::getLib('session')->set('is_test_sso', true);
		}
        $this->template()->assign(array(
                'sLink' => $link,
                'showSsoButton' => $showSsoButton,
                'sJanrainUrl' => (Phpfox::isModule('janrain') ? Phpfox::getService('janrain')->getUrl() : '')
            )
        );
	}
	
	/**
	 * Garbage collector. Is executed after this class has completed
	 * its job and the template has also been displayed.
	 */
	public function clean()
	{
		(($sPlugin = Phpfox_Plugin::get('user.component_block_login_header_clean')) ? eval($sPlugin) : false);
	}
}

?>