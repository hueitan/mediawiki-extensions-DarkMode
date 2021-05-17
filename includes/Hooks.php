<?php

namespace MediaWiki\Extension\DarkMode;

use Html;
use MediaWiki\MediaWikiServices;
use OutputPage;
use Skin;
use User;

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
		if ( $key === 'places' ) {
			$footerlinks['darkmode-link'] = Html::element( 'a', [ 'href' => '#', 'class' => 'darkmode-link' ], $skin->msg( 'darkmode-link' )->text() );
		}
	}

	/** 
	 * This will create a new sidebar item.
	 * This is usually the topmost section near the logo and is untitled.
 	 * @param Skin $skin
 	 * @param array $bar
 	 */
	public static function onSkinBuildSidebar( Skin $skin, array &$bar ) {
		$bar['Theme'] = Html::element( 'a', [ 'href' => '#', 'class' => 'darkmode-link' ], $skin->msg( 'darkmode-link' )->text() );;
	}

	/**
	 * Handler for BeforePageDisplay hook.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 * @param OutputPage $output
	 * @param Skin $skin Skin being used.
	 */
	public static function onBeforePageDisplay( OutputPage $output, Skin $skin ) {
		$output->addModules( 'ext.DarkMode' );
		$output->addModuleStyles( 'ext.DarkMode.styles' );

		$req = $output->getRequest();
		$user = $skin->getUser();
		if ( $req->getVal( 'usedarkmode' ) ) {
			self::toggleDarkMode( $output );
		} elseif ( MediaWikiServices::getInstance()->getUserOptionsLookup()->getBoolOption( $user, 'darkmode' ) ) {
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
}
