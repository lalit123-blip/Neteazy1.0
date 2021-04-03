import Raven from '../lib/Raven';
import { createBackgroundIframe } from '../iframe/iframe';
import { initApp } from './appUtils';
import { monitorFormPreviewRender } from '../api/hubspotPluginApi';

export function initBackgroundApp(initFn) {
  function main() {
    createBackgroundIframe();
    initFn();
  }
  initApp(main);
}

export function initMonitorGutenberBlockPreview() {
  const formsPreviewTimeouts = {};
  // This listener is responsible to monitor the form previews are rendered correctly
  window.addEventListener('message', event => {
    if (event.data.type === 'hsFormCallback') {
      const formId = event.data.id;
      if (event.data.eventName === 'onBeforeFormInit') {
        formsPreviewTimeouts[formId] = setTimeout(() => {
          Raven.captureMessage(
            `The form preview ${formId} has not been rendered correctly.`
          );
        }, 10 * 1000);
      }
      if (event.data.eventName === 'onFormReady') {
        clearTimeout(formsPreviewTimeouts[formId]);
        monitorFormPreviewRender();
      }
    }
  });
}
