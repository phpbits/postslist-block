/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { InspectorControls } = wp.editor;
const { Fragment, Component } = wp.element;
const { PanelBody, RangeControl } = wp.components;

/**
 * Inspector controls
 */
class Inspector extends Component {
	render() {
		const {
			attributes,
			setAttributes,
		} = this.props;

		const {
			postsToShow,
		} = attributes;

		return (
			<Fragment>
				<InspectorControls>
					<PanelBody title={ __( 'Post Settings', 'postslist-block' ) }>
						<RangeControl
							label={ __( 'Number of posts', 'postslist-block' ) }
							help={ __( 'Change the number of posts displayed.', 'postslist-block' ) }
							value={ postsToShow }
							onChange={ ( value ) => setAttributes( { postsToShow: value } ) }
							min={ 2 }
							max={ 20 }
						/>
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	}
}

export default Inspector;
