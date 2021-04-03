import Penpal from 'penpal';
import Raven from './Raven';
import {
  syncRoute,
  leadinPageReload,
  leadinPageRedirect,
  setLeadinUnAuthedNavigation,
} from '../navigation';
import * as leadinConfig from '../constants/leadinConfig';
import {
  leadinClearQueryParam,
  getQueryParam,
  filterLeadinQueryParams,
} from '../utils/queryParams';
import { leadinGetPortalInfo } from '../utils/portalInfo';
import {
  leadinConnectPortal,
  leadinDisconnectPortal,
  skipSignup,
} from '../api/wordpressAjaxClient';
import { makeProxyRequest } from '../api/wordpressApiClient';

const methods = {
  leadinClearQueryParam,
  leadinPageReload,
  leadinPageRedirect,
  leadinGetPortalInfo,
  leadinConnectPortal,
  leadinDisconnectPortal,
  getLeadinConfig: () => leadinConfig,
  skipSignup,
  setLeadinUnAuthedNavigation,
  makeProxyRequest,
};

const UNAUTHORIZED = 'unauthorized';
const REDIRECT = 'REDIRECT';
const hubspotBaseUrl = leadinConfig.hubspotBaseUrl;

function createConnectionToIframe(iframe) {
  return Penpal.connectToChild({
    url: iframe.src,
    // The iframe to which a connection should be made
    iframe,
    // Methods the parent is exposing to the child
    methods,
  });
}

export function initInterframe(iframe) {
  if (!window.leadinChildFrameConnection) {
    window.leadinChildFrameConnection = createConnectionToIframe(iframe);
    window.leadinChildFrameConnection.promise.catch(error => {
      Raven.captureException(error, {
        fingerprint: ['INTERFRAME_CONNECTION_ERROR'],
      });
    });
  }

  const redirectToLogin = event => {
    if (event.data === UNAUTHORIZED) {
      window.removeEventListener('message', redirectToLogin);
      iframe.src = leadinConfig.loginUrl;
      setLeadinUnAuthedNavigation();
    }
  };

  const handleNavigation = event => {
    if (event.origin !== hubspotBaseUrl) return;
    try {
      const data = JSON.parse(event.data);
      if (data['leadin_sync_route']) {
        const route = data['leadin_sync_route'];
        const search = data['leadin_sync_search'];

        syncRoute(route, filterLeadinQueryParams(search));
      } else if (data['message'] === REDIRECT) {
        window.location.href = data['url'];
      }
    } catch (e) {
      // Error in parsing message
    }
  };

  const currentPage = getQueryParam('page');
  if (currentPage !== 'leadin_settings' && currentPage !== 'leadin') {
    window.addEventListener('message', redirectToLogin);
  }

  window.addEventListener('message', handleNavigation);
}
