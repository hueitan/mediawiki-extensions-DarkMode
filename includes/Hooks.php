<?php

namespace MediaWiki\Extension\DarkMode;

use Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Minerva\Menu\Group;
use MinervaUI;
use OutputPage;
use Skin;
use User;
use BetaFeatures;

class Hooks {
	/**
	 * Handler for SkinAddFooterLinks hook.
	 * Add a "Dark mode" item to the footer.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinAddFooterLinks
	 * @param Skin $skin Skin being used.
	 * @param string $key Current position in the footer.
	 * @param array &$footerlinks Array of URLs to add to.
	 */
	public static function onSkinAddFooterLinks( Skin $skin, string $key, array &$footerlinks ) {
		if ( !Self::betaFeatureEnabled( $skin->getUser() ) ) {
			return;
		}

		if ( $key === 'places' ) {
			$footerlinks['darkmode-link'] = Html::element( 'a', [ 'href' => '#', 'class' => 'darkmode-link' ], $skin->msg( 'darkmode-link' )->text() );
		}
	}

	public static function onMobileMenu( string $name, Group $group ) {
		if ( in_array( $name, [ 'sitetools', 'user' ] ) ) {
			$group->insert( 'darkmode-link' )
				->addComponent(
						wfMessage( 'darkmode-label-msg' ) . ' always',
						'?setdarkmode=1', // @todo hide the menu bar
						MinervaUI::iconClass( 'moon', 'before' ) , // @todo complete with 'icon'
						[
							'data-event-name' => 'darkmode',
						]
				);
		}
	}

	/** 
	 * This will create a new sidebar item.
	 * This is usually the topmost section near the logo and is untitled.
 	 * @param Skin $skin
 	 * @param array $bar
 	 */
	public static function onSkinBuildSidebar( Skin $skin, array &$bar ) {
		if ( !Self::betaFeatureEnabled( $skin->getUser() ) ) {
			return;
		}

		$bar['Theme'] = Html::element( 'a', [ 'href' => '#', 'class' => 'darkmode-link' ], $skin->msg( 'darkmode-link' )->text() );;
	}

	/**
	 * Handler for BeforePageDisplay hook.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 * @param OutputPage $output
	 * @param Skin $skin Skin being used.
	 */
	public static function onBeforePageDisplay( OutputPage $output, Skin $skin ) {
		if ( !Self::betaFeatureEnabled( $skin->getUser() ) ) {
			return;
		}

		$output->addModules( 'ext.DarkMode' );
		$output->addModuleStyles( 'ext.DarkMode.styles' );

		$req = $output->getRequest();
		$user = $skin->getUser();

		if ( $req->getVal( 'setdarkmode' ) ) {
			MediaWikiServices::getInstance()->getUserOptionsManager()->setOption( $user, 'darkmode', 1 );
			self::toggleDarkMode( $output );
		} else if ( $req->getVal( 'usedarkmode' ) ) {
			self::toggleDarkMode( $output );
		} elseif ( MediaWikiServices::getInstance()->getUserOptionsManager()->getOption( $user, 'darkmode' ) ) {
			self::toggleDarkMode( $output );
		}
	}

	/**
	 * Allow others to toggle Dark Mode
	 * @param OutputPage $output
	 */
	public static function toggleDarkMode( OutputPage $output ) {
		$output->addHtmlClasses( 'client-dark-mode' );
	}

	/**
	 * Handler for GetPreferences hook
	 * Add hidden preference to keep dark mode turned on all pages
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GetPreferences
	 * @param User $user Current user
	 * @param array &$preferences
	 */
	public static function onGetPreferences( User $user, array &$preferences ) {
		$preferences['darkmode'] = [
			'type' => 'api',
			'default' => 0,
		];
	}

	public static function onGetBetaFeaturePreferences( User $user, array &$betaPrefs ) {
        $betaPrefs['darkmode-feature'] = [
            // The first two are message keys
            'label-message' => 'darkmode-label-msg',
            'desc-message' => 'darkmode-description-msg',
            // Paths to images that represents the feature.
            // The image is usually different for ltr and rtl languages.
            // Images for specific languages can also specified using the language code.
            'screenshot' => array(
                // 'ru' => "$extensionAssetsPath/MyExtension/images/screenshot-ru.png",
                // 'ltr' => "$extensionAssetsPath/MyExtension/images/screenshot-ltr.png",
                // 'rtl' => "$extensionAssetsPath/MyExtension/images/screenshot-rtl.png",
            ),
            // Link to information on the feature - use subpages on mw.org, maybe?
            'info-link' => 'https://www.mediawiki.org/wiki/Special:MyLanguage/Extension:MyExtension/DarkMode',
            // Link to discussion about the feature - talk pages might work
            'discussion-link' => 'https://www.mediawiki.org/wiki/Special:MyLanguage/Help_talk:Extension:MyExtension/DarkMode',
        ];
    }

	public static function betaFeatureEnabled( User $user ) {
		return BetaFeatures::isFeatureEnabled( $user, 'darkmode-feature' ) ;
	}
}
