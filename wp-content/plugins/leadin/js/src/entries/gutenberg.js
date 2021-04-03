import registerFormBlock from '../gutenberg/FormBlock/registerFormBlock';
import {
  initBackgroundApp,
  initMonitorGutenberBlockPreview,
} from '../utils/backgroundAppUtils';

initBackgroundApp(registerFormBlock);
initMonitorGutenberBlockPreview();
