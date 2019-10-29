/**
 * External dependencies
 */
import { isUndefined, pickBy, map } from 'lodash';
import classnames from 'classnames';
import moment from 'moment';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { compose } = wp.compose;
const { withSelect } = wp.data;
const { decodeEntities } = wp.htmlEntities;
const { Fragment, Component } = wp.element;
const { Placeholder, Spinner, withSpokenMessages } = wp.components;
/**
 * Block edit function
 */
class Edit extends Component {
	componentDidMount() {
		const {
			setAttributes,
		} = this.props;

		setAttributes( { paged: 1 } );
	}

	render() {
		const {
			attributes,
			setAttributes,
			postsList,
			className,
		} = this.props;

		const { paged } = attributes;

		const truncate = ( str, noWords ) => {
			return str.split( ' ' ).splice( 0, noWords ).join( ' ' );
		};

		// Check if there are posts
		const hasPosts = Array.isArray( postsList ) && postsList.length;

		//placeholder when there are no posts or still loading
		if ( ! hasPosts ) {
			return (
				<Fragment>
					<Placeholder
						icon="excerpt-view"
						label={ __( 'Posts List Block', 'postslist-block' ) }
					>
						{ ! Array.isArray( postsList ) ?
							<Spinner /> :
							__( 'No posts found.', 'postslist-block' )
						}
					</Placeholder>
				</Fragment>
			);
		}

		return (
			<Fragment>
				<section
					className={ classnames(
						className,
						'postslist-block',
					) }
				>
					{ map( postsList, ( post, i ) => {
						return (
							<article
								key={ i }
								id={ 'post-' + post.id }
								className={ classnames(
									'hentry',
									'post-' + post.id,
									post.type,
									'type-' + post.type,
									'status-' + post.status,
									'format-' + post.format,
									post.featured_image_src ? 'has-post-thumbnail' : null
								) }
							>
								<header className="entry-header">
									<h2>
										<a href={ post.link } target="_blank" rel="noopener noreferrer">
											{ decodeEntities( post.title.rendered.trim() ) || __( '(Untitled)', 'postslist-block' ) }
										</a>
									</h2>
									<div className="entry-meta">
										<span className="post-author">
											<a target="_blank" rel="noopener noreferrer" href={ post.author_info.author_link }>{ post.author_info.display_name }</a>
										</span>
										<span className="posted-on">
											<a href={ post.link } target="_blank" rel="noopener noreferrer">
												<time className="entry-date published" dateTime={ moment( post.date_gmt ).utc().format() }>
													{ moment( post.date_gmt ).local().format( 'MMMM DD, Y', 'postslist-block' ) }
												</time>
											</a>
										</span>
									</div>
								</header>
								{ post.featured_image_src ?
									<div className="entry-media">
										<a href={ post.link } className="post-thumbnail" target="_blank" rel="noopener noreferrer">
											<figure><img src={ post.featured_image_src } alt="" /></figure>
										</a>
									</div> :
									null }
								<div className="entry-summary">
									<div dangerouslySetInnerHTML={ { __html: truncate( post.excerpt.rendered, 55 ) } } />
								</div>

							</article>
						);
					} ) }
					<nav className="navigation pagination" role="navigation">
						<h2 className="screen-reader-text">{ __( 'Posts navigation', 'postslist-block' ) }</h2>
						<div className="nav-links">
							{ paged > 1 ? <a href="#" className="prev page-numbers" onClick={ () => {
								setAttributes( { paged: paged - 1 } );
							} }>{ __( '« Newer Posts', 'postslist-block' ) }</a> : '' }
							<a href="#" className="next page-numbers" onClick={ () => {
								setAttributes( { paged: paged + 1 } );
							} }>{ __( 'Older Posts »', 'postslist-block' ) }</a>
						</div>
					</nav>
				</section>
			</Fragment>
		);
	}
}

export default compose( [
	withSelect( ( select, props ) => {
		const {
			postsToShow,
			paged,
		} = props.attributes;

		const { getEntityRecords } = select( 'core' );

		const postsListQuery = pickBy( {
			per_page: postsToShow,
			offset: paged > 1 ? postsToShow * ( paged - 1 ) : 0,
			exclude: [ select( 'core/editor' ).getCurrentPostId() ],
		}, ( value ) => ! isUndefined( value ) );

		return {
			postsList: getEntityRecords( 'postType', 'post', postsListQuery ),
		};
	} ),
	withSpokenMessages,
] )( Edit );
