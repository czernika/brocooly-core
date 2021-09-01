<?php

declare(strict_types=1);

namespace Brocooly\Hooks;

class CustomPaginationPermalink
{
    public function load() {

		/**
		 * Disable the default WordPress canonical redirect - this is necessary,
		 * because WordPress will always redirect to the /2/ page when it encounters the page parameter in the URL or query args.
		 */
		remove_filter( 'template_redirect', 'redirect_canonical' );

		add_filter( 'paginate_links', [ $this, 'changePaginationPermalinks' ] );
		add_filter( 'get_pagenum_link', [ $this, 'changePaginationPermalinks' ] );

		/**
		 * It may throw 404 error with custom pagination.
		 * We will set posts_per_page manually to a global query
		 */
		add_action( 'pre_get_posts', [ $this, 'setCustomPostsPerPageQuery' ] );
	}

	private function changePaginationPermalinks( string $link ) {
		return preg_replace( '~/page/(\d+)/?~', '?paged=\1', $link );
	}
}
