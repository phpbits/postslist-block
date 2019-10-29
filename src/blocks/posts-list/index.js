/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Internal dependencies
 */
import metadata from './block.json';
import Edit from './components/edit';

/**
 * Block constants
 */
const { name, category, attributes } = metadata;
const title = __( 'Posts List', 'postslist-block' );
const description = __( 'Display posts list with defined number of items to be shown.', 'postslist-block' );
const keywords = [
	__( 'posts', 'postslist-block' ),
	__( 'post', 'postslist-block' ),
	__( 'blog', 'postslist-block' ),
];

const settings = {
	title,
	description,
	icon: 'excerpt-view',
	keywords,
	attributes,
	supports: {
		align: [ 'wide', 'full' ],
	},
	edit: Edit,
	save() {
		return null;
	},
};
export { name, category, settings };
