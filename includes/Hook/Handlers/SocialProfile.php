<?php

namespace MediaWiki\Skins\Cosmos\Hook\Handlers;

use Html;
use MediaWiki\Skins\Cosmos\CosmosSocialProfile;
use SpecialPage;
use UserProfilePage;

class SocialProfile {

	public function onUserProfileGetProfileTitle(
		UserProfilePage $userProfilePage,
		string &$profileTitle
	) {
		if (
			$this->config->get( 'CosmosSocialProfileShowGroupTags' ) ||
			$this->config->get( 'CosmosSocialProfileShowEditCount' ) ||
			$this->config->get( 'CosmosSocialProfileAllowBio' )
		) {
			$profileOwner = $userProfilePage->profileOwner;

			$groupTags = $this->config->get( 'CosmosSocialProfileShowGroupTags' )
				? CosmosSocialProfile::getUserGroups( $profileOwner )
				: null;

			if ( $this->config->get( 'CosmosSocialProfileShowEditCount' ) ) {
				$contribsUrl = SpecialPage::getTitleFor(
					'Contributions', $profileOwner->getName()
				)->getFullURL();

				$editCount = Html::closeElement( 'br' );

				$editCount .= Html::rawElement( 'div', [
					'class' => [ 'contributions-details', 'tally' ]
				], Html::rawElement( 'a', [
					'href' => $contribsUrl
				], Html::rawElement( 'em', [],
					(string)CosmosSocialProfile::getUserEdits( $profileOwner )
				) .
				Html::rawElement( 'span', [],
					wfMessage( 'cosmos-editcount-label' )->escaped() .
					Html::closeElement( 'br' ) .
					CosmosSocialProfile::getUserRegistration( $profileOwner )
				) ) );
			} else {
				$editCount = null;
			}

			// experimental
			$followBioRedirects = $this->config->get( 'CosmosSocialProfileFollowBioRedirects' );

			$bio = $this->config->get( 'CosmosSocialProfileAllowBio' )
				? CosmosSocialProfile::getUserBio( $profileOwner, $followBioRedirects )
				: null;

			$profileTitle = '<div class="hgroup"><h1 itemprop="name">' . $profileOwner->getName() . '</h1>' . $groupTags . $editCount . $bio . '</div>';
		}
	}
