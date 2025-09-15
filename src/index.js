
import { registerPlugin } from '@wordpress/plugins';
import NewsletterMetaFields from './components/NewsletterMetaFields';
import './components/SubscriptionFormBlock';

registerPlugin('canwbe-meta-fields-panel', {
    render: NewsletterMetaFields,
});
