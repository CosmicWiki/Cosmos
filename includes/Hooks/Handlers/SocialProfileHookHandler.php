<?php

namespace MediaWiki\Skins\Cosmos\Hooks\Handlers;

use Config;
use ConfigFactory;
use Html;
use Sanitizer;
use SpecialPage;
use TextContent;
use User;
use UserProfilePage;

class SocialProfileHookHandler {

	/** @var Config */
	private $config;

	/**
	 * @param ConfigFactory $configFactory
	 */
	public function __construct( ConfigFactory $configFactory ) {
		$this->config = $configFactory->makeConfig( 'Cosmos' );
	}

	/**
	 * Set up Cosmos-specific SocialProfile elements
	 *
	 * @param UserProfilePage $userProfilePage
	 * @param string &$profileTitle
	 */
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
				? $this->getUserGroups( $profileOwner )
				: null;

			$editCount = null;
			if ( $this->config->get( 'CosmosSocialProfileShowEditCount' ) ) {
				$contribsUrl = SpecialPage::getTitleFor(
					'Contributions', $profileOwner->getName()
				)->getFullURL();

				$editCount = Html::rawElement( 'div', [
					'class' => [ 'contributions-details', 'tally' ]
				], Html::rawElement( 'a', [
					'href' => $contribsUrl
				], Html::rawElement( 'em', [],
					$this->getUserEdits( $profileOwner )
				) .
				Html::rawElement( 'span', [],
					$userProfilePage->getContext()->msg( 'cosmos-editcount-label' )->escaped() .
					Html::closeElement( 'br' ) .
					$this->getUserRegistration( $profileOwner )
				) ) );
			}

			// experimental
			$followBioRedirects = $this->config->get( 'CosmosSocialProfileFollowBioRedirects' );

			$bio = $this->config->get( 'CosmosSocialProfileAllowBio' )
				? $this->getUserBio( $profileOwner->getName(), $followBioRedirects )
				: null;

			$profileTitle = Html::rawElement( 'div', [ 'class' => 'hgroup' ],
				Html::element( 'h1', [ 'itemprop' => 'name' ], $profileOwner->getName() ) .
				$groupTags
			) . $editCount . $bio;
		}
	}

	/**
	 * @param User $user
	 * @return string
	 */
	private function getUserRegistration( User $user ): string {
		return date( 'F j, Y', strtotime( $user->getRegistration() ) );
	}

	/**
	 * @param User $user
	 * @return string
	 */
	private function getUserGroups( User $user ): string {
		if ( $user->getBlock() ) {
			$userTags = Html::element(
				'span',
				[ 'class' => 'tag tag-blocked' ],
				wfMessage( 'cosmos-user-blocked' )->text()
			);
		} else {
			$numberOfTags = 0;
			$userTags = '';

			foreach ( $this->config->get( 'CosmosSocialProfileTagGroups' ) as $value ) {
				if ( in_array( $value, $this->userGroupManager->getUserGroups( $user ) ) ) {
					$numberOfTags++;
					$numberOfTagsConfig = $this->config->get( 'CosmosSocialProfileNumberofGroupTags' );
					$userGroupMessage = wfMessage( "group-{$value}-member" );

					if ( $numberOfTags <= $numberOfTagsConfig ) {
						$userTags .= Html::element(
							'span',
							[ 'class' => 'tag tag-' . Sanitizer::escapeClass( $value ) ],
							ucfirst( ( !$userGroupMessage->isDisabled() ? $userGroupMessage->text() : $value ) )
						);
					}
				}
			}
		}

		return $userTags;
	}

	/**
	 * @param User $user
	 * @return string
	 */
	private function getUserEdits( User $user ): string {
		return (string)$user->getEditCount();
	}

	/**
	 * @param string $user
	 * @param bool $followRedirects
	 * @return ?string
	 */
	private function getUserBio(
		string $user,
		bool $followRedirects
	): ?string {
		$userBioPage = $this->titleFactory->newFromText( "User:{$user}" )
			->getSubpage( 'bio' );

		if ( $userBioPage && $userBioPage->isKnown() ) {
			$wikiPage = $this->wikiPageFactory->newFromTitle( $userBioPage );

			$content = $wikiPage->getContent();

			// experimental
			if (
				$followRedirects &&
				$userBioPage->isRedirect() &&
				$content->getRedirectTarget()->isKnown() &&
				$content->getRedirectTarget()->inNamespace( NS_USER )
			) {
				$userBioPage = $content->getRedirectTarget();

				$wikiPage = $this->wikiPageFactory->newFromTitle( $userBioPage );

				$content = $wikiPage->getContent();
			}

			return $content instanceof TextContent
				? Html::element( 'p', [ 'class' => 'bio' ], $content->getText() )
				: null;
		}

		return null;
	}
}
