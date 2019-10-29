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
const { Placeholder, Spinner, Dashicon } = wp.components;
/**
 * Block edit function
 */
class Edit extends Component {

	render() {
		const {
			attributes,
			setAttributes,
			postsList,
			className,
		} = this.props;

		const truncate = ( str, no_words ) => {
			return str.split(' ').splice(0, no_words).join(' ');
		};

		// Check if there are posts
		const hasPosts = Array.isArray( postsList ) && postsList.length;
		console.log(postsList);
		
		//placeholder when there are no posts or still loading
		if (!hasPosts) {
			return (
				<Fragment>
					<Placeholder
						icon="excerpt-view"
						label={__('Posts List Block', 'postslist-block')}
					>
						{!Array.isArray(postsList) ?
							<Spinner /> :
							__('No posts found.', 'postslist-block')
						}
					</Placeholder>
				</Fragment>
			);
		}

		return(
			<Fragment>
				<section
					className={classnames(
						className,
						'postslist-block',
					)}
				>
					{ map(postsList, (post, i) => {
						return(
							<article
								key={i}
								id={'post-' + post.id}
								className={classnames(
									'hentry',
									'post-' + post.id,
									post.type,
									'type-' + post.type,
									'status-' + post.status,
									'format-' + post.format,
									post.featured_image_src ? 'has-post-thumbnail' : null
								)}
							>
								<header className="entry-header">
									<h2>
										<a href={post.link} target="_blank" rel="bookmark">
											{decodeEntities(post.title.rendered.trim()) || __('(Untitled)', 'postslist-block')}
										</a>
									</h2>
									<div className="entry-meta">
										<span class="post-author">
											<a target="_blank" href={post.author_info.author_link}>{post.author_info.display_name}</a>
										</span>
										<span class="posted-on">
											<a href={post.link} target="_blank">
												<span>{__('Posted on ', 'postslist-block')}</span>
												<time class="entry-date published" datetime={moment(post.date_gmt).utc().format()}>
													{moment(post.date_gmt).local().format('MMMM DD, Y', 'postslist-block')}
												</time>
											</a>
										</span>
									</div>
								</header>
								{post.featured_image_src ?
									<div className="entry-media">
										<a href={post.link} className="post-thumbnail" target="_blank" rel="bookmark">
											<figure><img src={post.featured_image_src} /></figure>
										</a>
									</div>
									: null}
								<div className="entry-summary">
									<div dangerouslySetInnerHTML={{ __html: truncate(post.excerpt.rendered, 200) }} />
								</div>

							</article>
						);
					}) }
					<nav class="navigation pagination" role="navigation">
						<h2 class="screen-reader-text">{__('Posts navigation', 'postslist-block') }</h2>
						<div className="nav-links">
							<a href="#" className="prev page-numbers"><Dashicon icon="arrow-left-alt2" /></a>
							<a href="#" className="next page-numbers"><Dashicon icon="arrow-right-alt2" /></a>
						</div>
					</nav>
				</section>
			</Fragment>
		);
	}
}

export default compose([
	withSelect((select, props) => {
		const {
			postsToShow
		} = props.attributes;
		
		const { getEntityRecords } = select('core');
		
		const postsListQuery = pickBy({
			per_page: postsToShow,
			exclude: [ select('core/editor').getCurrentPostId() ]
		}, (value) => !isUndefined(value));

		return {
			postsList: getEntityRecords('postType', 'post', postsListQuery)
		};
	})
])( Edit );