import { createConfirmationDialog } from './helpers';
import { showVzToast } from './vzShell';

// Displays page error message/notice in a confirmation dialog / toast
export default function handleErrorMessage() {
	const errorMessage = Alpine.store('globals').ERROR_MESSAGE;
	const okMessage = Alpine.store('globals').OK_MESSAGE;

	if (errorMessage) {
		showVzToast(errorMessage, 'danger');
		createConfirmationDialog({ message: errorMessage });
	} else if (okMessage) {
		showVzToast(okMessage, 'success');
	}
}
