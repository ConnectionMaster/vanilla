<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::Session();
?>
<div class="MessageForm EditCommentForm FormTitleWrapper">
   <div class="Form-BodyWrap">
      <div class="Form-Body">
         <div class="FormWrapper FormWrapper-Condensed">
            <?php
            echo $this->Form->Open();
            echo $this->Form->Errors();
            echo $this->Form->BodyBox('Body', array('Table' => 'Comment', 'tabindex' => 1));
            echo "<div class=\"Buttons\">\n";
			$this->FireEvent('BeforeFormButtons');
            echo $this->Form->Button('Save Comment', array('class' => 'button Button Primary CommentButton', 'tabindex' => 2));
            echo Wrap(Anchor(T('Cancel'), '/'), 'span class="button secondary Cancel"');
            echo "</div>\n";
            echo $this->Form->Close();
            ?>
         </div>
      </div>
   </div>
</div>