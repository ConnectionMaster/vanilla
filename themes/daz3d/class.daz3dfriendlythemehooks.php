<?php if (!defined('APPLICATION')) exit();

class Daz3DThemeHooks implements Gdn_IPlugin {

	public function Setup() { }

	public function OnDisable() { }

	public function Base_Render_Before($Sender) {
		$Sender->AddJsFile('custom.js');
	}

	// add view users posts links
	public function ProfileController_BeforeProfileOptions_Handler($Sender, $Args) {
		if (Gdn::Session()->IsValid()) {
			$person = (($Sender->User->UserID == Gdn::Session()->UserID) ? 'Your' : 'View User\'s');

			// Add user posts links to dropdown
			$Args['ProfileOptions'][] = array('Text' => "{$person} Threads",
				'Url' => "/profile/discussions/{$Sender->User->UserID}/".Gdn_Format::Url($Sender->User->Name));
			$Args['ProfileOptions'][] = array('Text' => "{$person} Posts",
				'Url' => "/profile/comments/{$Sender->User->UserID}/".Gdn_Format::Url($Sender->User->Name));

			// only show inbox link if permitted
			if (($Sender->User->UserID == Gdn::Session()->UserID) || CheckPermission('Conversations.Moderation.Manage')) {
				$Args['ProfileOptions'][] = array('Text' => "{$person} Inbox",
					'Url' => "/messages/all?userid={$Sender->User->UserID}");
			}
		}
	}

	/**
	 * This function hooks the Smarty init to add our directory
	 * containing our custom Smarty functions
	 *
	 * @param object $sender Smarty object.
	 * @return void
	 */
	public function gdn_smarty_init_handler($sender) {
		// add directory "/themes/MyTheme/SmartyPlugins/"
		$sender->plugins_dir[] = dirname(__FILE__).DS.'SmartyPlugins';
	}

	/*
	public function SettingsController_AfterCurrentTheme_Handler($Sender) {
		$SingleColumn = C('Themes.EmbedFriendly.SingleColumn');
		echo Wrap(
			T('This theme allows you to hide the side panel next to your forum and conversations. This is super handy if the website you are embedding in does not have a lot of width to squeeze into.')
			.Wrap(Anchor(
				T($SingleColumn ? 'Show the side panel' : 'Hide the side panel'),
				'settings/embedfriendlytogglepanel/'.Gdn::Session()->TransientKey(),
				'SmallButton'
			), 'div')
		, 'div', array('class' => 'Description'));
	}

	public function SettingsController_EmbedFriendlyTogglePanel_Create($Sender) {
		$this->_TogglePanel($Sender);
		Redirect('settings/themes');
	}

	public function PluginController_BeforeEmbedRecommend_Handler($Sender) {
		$SingleColumn = C('Themes.EmbedFriendly.SingleColumn');
		echo '<div class="EmbedRecommend">
			<strong>Theme Options</strong>'
			.Wrap(
				T('This theme allows you to hide the side panel next to your forum and conversations. This is super handy if the website you are embedding in does not have a lot of width to squeeze into.')
				.Wrap(Anchor(
					T($SingleColumn ? 'Show the side panel' : 'Hide the side panel'),
					'plugin/embedfriendlytogglepanel/'.Gdn::Session()->TransientKey(),
					'SmallButton'
				), 'div', array('style' => 'margin-top: 10px;'))
			, 'em')
		.'</div>';
	}

	public function PluginController_EmbedFriendlyTogglePanel_Create($Sender) {
		$this->_TogglePanel($Sender);
		Redirect('plugin/embed');
	}

	private function _TogglePanel($Sender) {
		$Sender->Permission('Garden.Settings.Manage');
		$TransientKey = GetValue(0, $Sender->RequestArgs);
		if (Gdn::Session()->ValidateTransientKey($TransientKey))
			SaveToConfig('Themes.EmbedFriendly.SingleColumn', C('Themes.EmbedFriendly.SingleColumn') ? FALSE : TRUE);
	}

   public function CategoriesController_Render_Before($Sender) {
		$this->_AddButton($Sender, 'NewDiscussionModule');
   }

   public function DiscussionsController_Render_Before($Sender) {
		$this->_AddButton($Sender, 'NewDiscussionModule');
   }

   public function DiscussionController_Render_Before($Sender) {
		$this->_AddButton($Sender, 'NewDiscussionModule');
   }

   public function DraftsController_Render_Before($Sender) {
		$this->_AddButton($Sender, 'NewDiscussionModule');
   }

	public function MessagesController_Render_Before($Sender) {
		$this->_AddButton($Sender, 'NewConversationModule');
	}

	private function _AddButton($Sender, $ModuleName) {
		if (C('Themes.EmbedFriendly.SingleColumn'))
			$Sender->AddModule($ModuleName, 'Content');
	}
   */
}
