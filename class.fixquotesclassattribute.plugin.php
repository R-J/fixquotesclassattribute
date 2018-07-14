<?php

class FixQuotesClassAttributePlugin extends Gdn_Plugin {
    public function onDisable() {
        removeFromConfig('FixQuotesClassAttribute');
    }

    public function settingsController_fixQuotesClassAttribute_create($sender) {
        $sender->permission('Garden.Settings.Manage');

        $sender->setHighlightRoute('dashboard/settings/plugins');
        $sender->setData('Title', t('Fix Quotes Class Attribute'));
        if ($sender->Form->authenticatedPostBack()) {
            if ($this->fixer('Discussion') === true && $this->fixer('Comment') === true) {
                $sender->informMessage('All posts have been processed now.');
            }
        }
        $sender->render('settings', '', '/plugins/fixquotesclassattribute');
    }

    /**
     * Endpoint for updating single posts.
     *
     * @param PluginController $sender Instance of the calling class.
     *
     * @return void.
     */
    public function pluginController_fixQuotesClassAttribute_create($sender) {
        $sender->permission('Garden.Settings.Manage');

        $postType = strtolower($sender->Request->get('type'));
        if ($postType == 'comment') {
            $postType = 'Comment';
        } elseif ($postType == 'discussion') {
            $postTypee = 'Discussion';
        } else {
            decho('Unknown post type "'.$postType.'"');
            return;
        }

        $id = $sender->Request->get('id');
        $this->fixer($postType, $id);
        
        decho("{$postType} #{$id} has been processed.");
    }

    /**
     * This method is updating the posts.
     *
     * @param string $postType Either Comment or Discussion.
     * @param  string $textColumn This allows flexibility if another table must
     *                            be fixed which doesn't store its content in a
     *                            column called "Body".
     *
     * @return boolean|null Returns true if no action needs to be taken.
     */
    private function fixer($postType, $id = 0, $textColumn = 'Body') {
        if ($id > 0) {
            Gdn::sql()->where("{$postType}ID", $id);
        }
        $lastID = c("FixQuotesClassAttribute.Last{$postType}ID", 0);
        $posts = Gdn::sql()
            ->select("{$postType}ID, {$textColumn}")
            ->from($postType)
            ->where("{$postType}ID >=", $lastID)
            ->where('Format', 'Html')
            ->limit(10000)
            ->get()
            ->resultArray();
        if (count($posts) == 0) {
            return true;
        } else {
            foreach ($posts as $post) {
                if (strpos($post[$textColumn], '<blockquote rel="') !== false) {
                    $body = preg_replace(
                        '/(<blockquote )(rel="[^"]*">)/',
                        '$1class="Quote" $2',
                        $post[$textColumn]
                    );
                    Gdn::sql()
                        ->update($postType)
                        ->set($textColumn, $body)
                        ->where("{$postType}ID", $post["{$postType}ID"])
                        ->put();
                }
                // Don't update if this is a single post for testing.
                if ($id = 0) {
                    saveToConfig(
                        "FixQuotesClassAttribute.Last{$postType}ID",
                        $post["{$postType}ID"]
                    );
                }
            }
        }
    }
}
