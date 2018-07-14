<?php defined('APPLICATION') or die ?>

<?= heading($this->title()) ?>
<div class="warning">Be sure you have a working backup before starting the process!
</div>
<div class="description"><p>The plugin searches for html blockquote tags which do not have the class "Quote" and so they do not show up correctly. The class is added to the html tag in the database.</p>
<p>This action will take very much time and you most probably will face a timeout. Simply press the button, wait for a few minutes, reload this page and do this again and again until you receive a feedback that there are no more posts to process.</p>
<p>You can update single posts for test purposes by visiting yourforum.com/plugin/fixquotesclassattribute?type=comment&id=500 where type can be either comment or discussion and id is the number of the post that should be updated.</p>
</div>

<?= $this->Form->open(), $this->Form->errors() ?>
<?= $this->Form->close('Start') ?>
