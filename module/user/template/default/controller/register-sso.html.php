<?php

defined('PHPFOX') or exit('NO DICE!'); ?>

{literal}
    <script type="text/javascript">
        $Behavior.termsAndPrivacy = function()
        {
            $('#js_terms_of_use').click(function()
            {
                {/literal}
                tb_show('{phrase var='user.terms_of_use' phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true}', $.ajaxBox('page.view', 'height=410&width=600&title=terms'));
                {literal}
                return false;
            });

            $('#js_privacy_policy').click(function()
            {
                {/literal}
                tb_show('{phrase var='user.privacy_policy' phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true}', $.ajaxBox('page.view', 'height=410&width=600&title=policy'));
                {literal}
                return false;
            });
        }
    </script>
{/literal}

<div id="main_registration_form">

    <h1>{phrase var='user.sso_sign_up_for_ssitetitle'}</h1>
    <div class="extra_info">
        {phrase var='user.sso_join_ssitetitle_to_connect_with_friends_share_photos_and_create_your_own_profile'}
    </div>
    <div id="main_registration_form_holder">
        {if isset($sCreateJs)}
            {$sCreateJs}
        {/if}
        <div id="js_signup_error_message" style="width:350px;"></div>
        {if Phpfox::getParam('user.allow_user_registration')}
            <div class="main_break" id="js_registration_holder">
                <form method="post" action="{url link='user.register-sso'}" id="js_form" enctype="multipart/form-data">
                {token}
                    <div id="js_signup_block">
                        <div id="js_register_step1">
                            <input type="hidden" name="val[full_name]" id="full_name" value="stock" size="30" />
                            {if $bIsPosted}
                                <input type="hidden" name="val[first_name]" id="first_name" value="{value type='input' id='first_name'}" size="30" />
                                <input type="hidden" name="val[last_name]" id="last_name" value="{value type='input' id='last_name'}" size="30" />
                                <input type="hidden" name="val[email]" id="email" value="{value type='input' id='email'}" size="30" />
                            {else}
                                <input type="hidden" name="val[first_name]" id="first_name" value="{$aForms.first_name}" size="30" />
                                <input type="hidden" name="val[last_name]" id="last_name" value="{$aForms.last_name}" size="30" />
                                <input type="hidden" name="val[email]" id="email" value="{$aForms.email}" size="30" />
                            {/if}

                            {template file='user.block.custom'}

                            {if Phpfox::getParam('core.registration_enable_dob')}
                            <div class="table">
                                <div class="table_left">
                                    {required}{phrase var='user.birthday'}:
                                </div>
                                <div class="table_right">
                                    {select_date start_year=$sDobStart end_year=$sDobEnd field_separator=' / ' field_order='MDY' bUseDatepicker=false sort_years='DESC'}
                                </div>
                            </div>
                            {/if}
                            {if Phpfox::getParam('core.registration_enable_gender')}
                            <div class="table">
                                <div class="table_left">
                                    <label for="gender">{required}{phrase var='user.i_am'}:</label>
                                </div>
                                <div class="table_right">
                                    {select_gender}
                                </div>
                            </div>
                            {/if}
                            {if Phpfox::getParam('core.registration_enable_location')}
                            <div class="table">
                                <div class="table_left">
                                    <label for="country_iso">{required}{phrase var='user.location'}:</label>
                                </div>
                                <div class="table_right">
                                    {select_location}
                                    {module name='core.country-child' country_force_div=true}
                                </div>
                            </div>
                            {/if}
                            {if Phpfox::getParam('core.city_in_registration')}
                            <div class="table">
                                <div class="table_left">
                                    <label for="city_location">{phrase var='user.city'}:</label>
                                </div>
                                <div class="table_right">
                                    <input type="text" name="val[city_location]" id="city_location" value="{value type='input' id='city_location'}" size="30" />
                                </div>
                            </div>
                            {/if}
                            {if Phpfox::getParam('core.registration_enable_timezone')}
                            <div class="table">
                                <div class="table_left">
                                    {phrase var='user.time_zone'}:
                                </div>
                                <div class="table_right">
                                    <select name="val[time_zone]">
                                        {foreach from=$aTimeZones key=sTimeZoneKey item=sTimeZone}
                                        <option value="{$sTimeZoneKey}"{if (Phpfox::getTimeZone() == $sTimeZoneKey && !isset($iTimeZonePosted)) || (isset($iTimeZonePosted) && $iTimeZonePosted == $sTimeZoneKey) || (Phpfox::getParam('core.default_time_zone_offset') == $sTimeZoneKey)} selected="selected"{/if}>{$sTimeZone}</option>
                                        {/foreach}
                                    </select>
                                </div>
                                <div class="clear"></div>
                            </div>
                            {/if}
                            {if Phpfox::isModule('subscribe') && Phpfox::getParam('subscribe.enable_subscription_packages') && count($aPackages)}
                            <div class="separate"></div>
                            <div class="table">
                                <div class="table_left">
                                    {if Phpfox::getParam('subscribe.subscribe_is_required_on_sign_up')}{required}{/if}{phrase var='user.membership'}:
                                </div>
                                <div class="table_right">
                                    <select name="val[package_id]" id="js_subscribe_package_id">
                                        {if Phpfox::getParam('subscribe.subscribe_is_required_on_sign_up')}
                                        <option value=""{value type='select' id='package_id' default='0'}>{phrase var='user.select'}:</option>
                                        {else}
                                        <option value=""{value type='select' id='package_id' default='0'}>{phrase var='user.free_normal'}</option>
                                        {/if}
                                        {foreach from=$aPackages item=aPackage}
                                        <option value="{$aPackage.package_id}"{value type='select' id='package_id' default=''$aPackage.package_id''}>{if $aPackage.show_price}({if $aPackage.default_cost == '0.00'}{phrase var='subscribe.free'}{else}{$aPackage.default_currency_id|currency_symbol}{$aPackage.default_cost}{/if}) {/if}{$aPackage.title|convert|clean}</option>
                                        {/foreach}
                                    </select>
                                    <div class="extra_info">
                                        <a href="#" onclick="tb_show('{phrase var='user.membership_upgrades' phpfox_squote=true}', $.ajaxBox('subscribe.listUpgradesOnSignup', 'height=400&width=500')); return false;">{phrase var='user.click_here_to_learn_more_about_our_membership_upgrades'}</a>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                            {/if}
                        </div>

                        {module name='user.showspamquestion'}

                        {if Phpfox::getParam('user.force_user_to_upload_on_sign_up')}
                        <div class="separate"></div>
                        <div class="table">
                            <div class="table_left">
                                {required}{phrase var='user.profile_image'}:
                            </div>
                            <div class="table_right">
                                <input type="file" name="image" />
                                <div class="extra_info">
                                    {phrase var='user.you_can_upload_a_jpg_gif_or_png_file'}
                                </div>
                            </div>
                        </div>
                        {/if}
                    </div>
                    {if Phpfox::isModule('captcha') && Phpfox::getParam('user.captcha_on_signup')}
                    <div id="js_register_capthca_image"{if Phpfox::getParam('user.multi_step_registration_form') && !isset($bIsPosted)} style="display:none;"{/if}>
                        {module name='captcha.form'}
                    </div>
                    {/if}

                    {if Phpfox::getParam('user.new_user_terms_confirmation')}
                    <div id="js_register_accept">
                        <div class="table">
                            <div class="table_clear">
                                <input type="checkbox" name="val[agree]" id="agree" value="1" class="checkbox v_middle" {value type='checkbox' id='agree' default='1'}/> {required}{phrase var='user.i_have_read_and_agree_to_the_a_href_id_js_terms_of_use_terms_of_use_a_and_a_href_id_js_privacy_policy_privacy_policy_a'}
                            </div>
                        </div>
                    </div>
                    {/if}

                    <div class="table_clear">
                    {if isset($bIsPosted) || !Phpfox::getParam('user.multi_step_registration_form')}
                        <input type="submit" value="{phrase var='user.sso_sign_up'}" class="button_register" id="js_registration_submit" />
                    {else}
                        <input type="button" value="{phrase var='user.sso_sign_up'}" class="button_register" id="js_registration_submit" onclick="$Core.registration.submitForm();" />
                    {/if}
                    </div>
                </form>
            </div>
        {/if}
    </div>
</div>

{literal}
<script>
    window.onload = function() {
        $('#js_form').submit(function(evt){
            $(".error_message").each(function(){$(this).remove();});
            var bIsValid = true;

            if ($('#country_iso').val() != '' && $('#js_country_child_id_value').val() == 0) {
                bIsValid = false;
                $('#js_country_child_id').message('{/literal}{phrase var='user.add_current_user_facility'}{literal}', 'error');
                $('#country_iso').addClass('alert_input');
            }

            if (bIsValid == false) {
                evt.preventDefault();
            }
        });
    }
</script>
{/literal}