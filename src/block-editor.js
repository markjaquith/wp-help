import { CheckboxControl } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { registerPlugin } from '@wordpress/plugins';

const DEFAULT_DOCUMENT = 'is_default_doc';

const select = withSelect(select => ({
	isDefault: select('core/editor').getEditedPostAttribute(DEFAULT_DOCUMENT) || false,
}));

const dispatch = withDispatch(dispatch => ({
	setDefaultDocument: isDefault => {
		dispatch('core/editor').editPost({[DEFAULT_DOCUMENT]: isDefault});
	},
}));

function WpHelp ({ isDefault, setDefaultDocument }) {
	return (
		<PluginPostStatusInfo>
			<CheckboxControl
				label="Default Help Document"
				checked={isDefault}
				onChange={setDefaultDocument}
			/>
		</PluginPostStatusInfo>
	);
}

registerPlugin('wp-help', {
	render: compose([select, dispatch])(WpHelp),
});
