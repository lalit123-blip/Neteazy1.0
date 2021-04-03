import $ from 'jquery';
import { healthcheckRestApi } from '../api/wordpressApiClient';
import Raven, { configureRaven } from '../lib/Raven';

export function initApp(initFn) {
  configureRaven();
  Raven.context(initFn);
}

export function initAppOnReady(initFn) {
  function main() {
    $(document).ready(initFn);
  }
  initApp(main);

  healthcheckRestApi().catch(error =>
    Raven.captureMessage(
      `WP Rest API healthcheck failed: ${error.responseText}`
    )
  );
}
