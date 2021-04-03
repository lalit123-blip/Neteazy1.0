import $ from 'jquery';

import Raven from '../lib/Raven';
import { restNonce, restUrl } from '../constants/leadinConfig';

function makeRequest(method, path, data = {}) {
  const restApiUrl = `${restUrl}leadin/v1${path}`;
  return new Promise((resolve, reject) => {
    $.ajax({
      url: restApiUrl,
      data: JSON.stringify(data),
      method,
      contentType: 'application/json',
      beforeSend: xhr => xhr.setRequestHeader('X-WP-Nonce', restNonce),
      success: resolve,
      error: response => {
        Raven.captureMessage(
          `HTTP Request to ${restApiUrl} failed with error ${response.status}: ${response.responseText}`,
          { fingerprint: ['WP Rest API Error'] }
        );
        reject(response);
      },
    });
  });
}

export function makeProxyRequest(method, hubspotApiPath, data = {}) {
  const proxyApiPath = `/proxy${hubspotApiPath}`;

  return makeRequest(method, proxyApiPath, data);
}

export function healthcheckRestApi() {
  return makeRequest('get', '/healthcheck');
}
