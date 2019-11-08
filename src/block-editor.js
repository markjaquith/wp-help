const { PanelRow, CheckboxControl } = wp.components;
const { withInstanceId, compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;
const { Fragment, Component } = wp.element;
const { PluginPostStatusInfo } = wp.editPost;
const { registerPlugin } = wp.plugins;

class WpHelp extends Component {
	constructor(props) {
		super(props);
		console.log(props);
	}

	state = {
		checked: false,
	};

	checked() {
		return this.state.checked;
	}

	toggleDefaultDoc = () => {
		this.setState(state => ({
			checked: !state.checked,
		}));
	};

	updateDefaultDoc(enabled) {
		const { meta, onUpdateDefaultDoc } = this.props;
		onUpdateDefaultDoc(meta, enabled);
	}

	render() {
		const {checked} = this.state;
		return (
			<Fragment>
				<PluginPostStatusInfo>
					<CheckboxControl
						label="Default Help Document"
						checked={checked}
						onChange={this.toggleDefaultDoc}
					/>
				</PluginPostStatusInfo>
			</Fragment>
		);
	}
}

const ConnectedWpHelp = compose([
	// withSelect(select => ({
	// 	meta: select('core/editor').getEditedPostAttribute('meta'),
	// })),
	// withDispatch(dispatch => ({
	// 	onUpdateLink: (meta, link) => {
	// 		dispatch('core/editor').editPost({ meta: { ...meta, _links_to: link } });
	// 	},
	// 	onUpdateDefaultDoc: (meta, enabled) => {
	// 		dispatch('core/editor').editPost({
	// 			meta: { ...meta, _links_to_target: enabled ? '_blank' : '' },
	// 		});
	// 	},
	// })),
	withInstanceId,
])(WpHelp);

registerPlugin('wp-help', {
	render: ConnectedWpHelp,
});
