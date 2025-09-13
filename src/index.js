
import { registerPlugin } from '@wordpress/plugins';
import NewsletterMetaFields from './components/NewsletterMetaFields';

registerPlugin('canwbe-meta-fields-panel', {
    render: NewsletterMetaFields,
});
