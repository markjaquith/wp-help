const { PanelRow, CheckboxControl } = wp.components;
const { withInstanceId, compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;
const { Fragment, Component } = wp.element;
const { PluginPostStatusInfo } = wp.editPost;
const { registerPlugin } = wp.plugins;

const DEFAULT_DOCUMENT = '_cws_wp_help_default_doc';

class WpHelp extends Component {
	constructor(props) {
		super(props);
		this.state = {
			checked: props.isDefault || false,
		};
	}

	checked = () => !! this.state.checked;

	toggleDefaultDoc = checked => {
		this.setState({checked});
		this.props.setDefaultDocument(checked);
	}

	render() {
		return (
			<Fragment>
				<PluginPostStatusInfo>
					<CheckboxControl
						label="Default Help Document"
						checked={this.checked()}
						onChange={this.toggleDefaultDoc}
					/>
				</PluginPostStatusInfo>
			</Fragment>
		);
	}
}

const ConnectedWpHelp = compose([
	withSelect(select => ({
		isDefault: (select('core/editor').getEditedPostAttribute('meta') || [])[DEFAULT_DOCUMENT] || false,
	})),
	withDispatch(dispatch => ({
		setDefaultDocument: isDefault => {
			dispatch('core/editor').editPost({ meta: {[DEFAULT_DOCUMENT]: isDefault, foo: 'bar' } });
		},
	})),
	withInstanceId,
])(WpHelp);

registerPlugin('wp-help', {
	render: ConnectedWpHelp,
});
