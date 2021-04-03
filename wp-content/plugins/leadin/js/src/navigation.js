import $ from 'jquery';

import { domElements } from './constants/selectors';
import urlsMap from './constants/urlsMap';
import { i18n, oauth } from './constants/leadinConfig';

function setSelectedMenuItem(url) {
  $(domElements.subMenuButtons).removeClass('current');
  const pageParam = url.match(/\?page=leadin_?\w*/)[0];
  const selectedElement = $(`a[href="admin.php${pageParam}"]`);
  selectedElement.parent().addClass('current');
}

// Given a route like "/settings/forms", parse it into "?page=leadin_settings&leadin_route[0]=forms"
export function syncRoute(path = '', searchQuery = '') {
  if (!oauth) {
    const baseUrls = Object.keys(urlsMap).sort((a, b) =>
      a.length < b.length ? 1 : -1
    );
    let wpPage;
    let route;

    baseUrls.some(basePath => {
      if (path.indexOf(basePath) === 0) {
        wpPage = urlsMap[basePath][0];
        const routePrefix = urlsMap[basePath][1] || '';
        const cleanedPath = path.replace(basePath, '');
        route = `${routePrefix}${cleanedPath}`.replace(/^\/+/, '');
        return true;
      }
      return false;
    });

    if (!wpPage) {
      return;
    }

    const leadinRouteParam = route
      ? `&${route
          .split('/')
          .map(
            (subRoute, index) =>
              `${encodeURIComponent(`leadin_route[${index}]`)}=${subRoute}`
          )
          .join('&')}`
      : '';

    const leadinSearchParam = searchQuery.length
      ? `&leadin_search=${encodeURIComponent(searchQuery)}`
      : '';

    const newUrl = `?page=${wpPage}${leadinRouteParam}${leadinSearchParam}`;

    setSelectedMenuItem(newUrl);
    window.history.replaceState(null, null, newUrl);
  }
}

export function disableNavigation() {
  $(domElements.allMenuButtons).off('click');
}

function filterAuthedMenuItems(menuItems) {
  let authedMenuItems = menuItems
    .filter(':not(.current)')
    .has(':not(a[href="admin.php?page=leadin_settings"])');

  if (authedMenuItems.length !== menuItems.length - 3) {
    authedMenuItems = authedMenuItems.filter(':not(.wp-first-item)');
  }

  return authedMenuItems;
}

export function setLeadinUnAuthedNavigation() {
  const itemsToRemove = filterAuthedMenuItems($(domElements.subMenuButtons));
  itemsToRemove.remove();

  const buttonToChangeText = $(domElements.subMenuButtons)
    .filter(':not(:contains(Settings))')
    .children();
  buttonToChangeText.text(i18n.signIn);
}

export const leadinPageReload = () => window.location.reload(true);

export const leadinPageRedirect = path => {
  syncRoute(path);
  leadinPageReload();
};
