<?php
/*
 * TODO: Disable mail notification about user registration for all admins from IP 195.24.142.141
 */
if (!defined('PHPFOX_INSTALLER') && Phpfox::getParam('user.approve_users'))
{
    $ssoRegistration = false;
    $aAdminIds = $this->database()
                ->select('u.user_id')
                ->from(Phpfox::getT('user'), 'u')
                ->where('u.user_group_id = 1')
                ->execute('getRows');

	/*$aCustomIds = Phpfox::getLib('database')
                ->select('ucmv.user_id')
                ->from(Phpfox::getT('user_custom_multiple_value'), 'ucmv')
                ->where('ucmv.option_id = 38')
                ->execute('getRows');*/
				
	//Add custom users ids here, separated by comma
	$aUserIds = '77';
	$aCustomIds = array();
	foreach(explode(',',$aUserIds) as $key => $value){
		$aCustomIds[$key]['user_id'] = $value;
	}
	if(count($aCustomIds)){
		$aAdminIds = array_merge($aAdminIds,$aCustomIds);
	}

    $aNewUser = $aInsert;

    if(empty($aNewUser['user_name']))
    {
        if (Phpfox::getParam('user.profile_use_id') || Phpfox::getParam('user.disable_username_on_sign_up'))
        {
            $aNewUser['user_name'] = 'profile-' . $iId;
        }
    }

    if (isset($aVals['isSso']) && $aVals['isSso'] === true ) {
        $regMailSubject = 'usernotif.sso_subject_user_joined_the_community';
        $regMailBody = 'usernotif.sso_user_joined_the_community';
    } else {
        $regMailSubject = 'usernotif.subject_user_pending_approval';
        $regMailBody = 'usernotif.pending_approval_user';
    }

    $aNewUser['user_link'] = Phpfox::getLib('url')->makeUrl($aNewUser['user_name']);

	foreach($aAdminIds as $iAdminId) {
		Phpfox::getLib('mail')
			->to($iAdminId)
			->subject(array($regMailSubject, array('site_title' => Phpfox::getParam('core.site_title'))))
			->message(array(
					$regMailBody, array(
						'full_name' => $aNewUser['full_name'],
						'user_link' => $aNewUser['user_link'],
						'email' => $aNewUser['email'],
					)
				)
			)
			->send();
	}
	
    unset($aNewUser);
    unset($aAdminIds);
} 
?>
