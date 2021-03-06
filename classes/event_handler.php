<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2015, Sergey Kambalin
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package followlist.classes
 */
class FOLLOWLIST_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var FOLLOWLIST_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FOLLOWLIST_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        
    }
    
    public function onCollectPrivacyActionList( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $action = array(
            'key' => 'followers_view',
            'pluginKey' => 'followlist',
            'label' => $language->text('followlist', 'privacy_action_view_followers'),
            'description' => '',
            'defaultValue' => 'everybody'
        );

        $event->add($action);
    }
    
    public function onCollectQuickLinks( BASE_CLASS_EventCollector $event )
    {
        $userId = OW::getUser()->getId();

        $count = FOLLOWLIST_CLASS_NewsfeedBridge::getInstance()
                ->getFollowingUsersCount(FOLLOWLIST_CLASS_NewsfeedBridge::FEED_TYPE_USER, $userId);
        
        if ( empty($count) )
        {
            return;
        }

        $url = OW::getRouter()->urlForRoute("followlist-user-followers", array(
            "userName" => OW::getUser()->getUserObject()->username
        ));
        
        $event->add(array(
            BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => OW::getLanguage()->text('followlist', 'quick_links_label'),
            BASE_CMP_QuickLinksWidget::DATA_KEY_URL => $url,
            BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => $count,
            BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => $url
        ));
    }
    
    public function onAddAdminNotifications( BASE_CLASS_EventCollector $e )
    {
        $language = OW::getLanguage();
        $e->add($language->text('followlist', 'admin_plugin_required_notification', array(
            'pluginUrl' => 'http://www.oxwall.org/store/item/43'
        )));
    }
    
       
    public function init()
    {
        if ( !FOLLOWLIST_CLASS_NewsfeedBridge::getInstance()->isActive() )
        {
            OW::getEventManager()->bind('admin.add_admin_notification', array($this, 'onAddAdminNotifications'));
            
            return;
        }
        
        FOLLOWLIST_CLASS_NewsfeedBridge::getInstance()->init();
        FOLLOWLIST_CLASS_SnippetsBridge::getInstance()->init();
        FOLLOWLIST_CLASS_HintBridge::getInstance()->init();
        
        OW::getEventManager()->bind('base.add_quick_link', array($this, 'onCollectQuickLinks'));
        OW::getEventManager()->bind('plugin.privacy.get_action_list', array($this,'onCollectPrivacyActionList'));
    }
}