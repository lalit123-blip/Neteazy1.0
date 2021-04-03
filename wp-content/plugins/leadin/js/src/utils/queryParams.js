import $ from 'jquery';

import { leadinQueryParamsKeys } from '../constants/leadinConfig';

export function leadinClearQueryParam() {
  let currentWindowLocation = window.location.toString();
  if (currentWindowLocation.indexOf('?') > 0) {
    currentWindowLocation = currentWindowLocation.substring(
      0,
      currentWindowLocation.indexOf('?')
    );
  }
  const newWindowLocation = `${currentWindowLocation}?page=leadin`;
  window.history.pushState({}, '', newWindowLocation);
}

export function getQueryParam(key) {
  const query = window.location.search.substring(1);
  const vars = query.split('&');
  for (let i = 0; i < vars.length; i++) {
    const pair = vars[i].split('=');
    if (decodeURIComponent(pair[0]) === key) {
      return decodeURIComponent(pair[1]);
    }
  }
  return null;
}

export function filterLeadinQueryParams(searchString) {
  if (!searchString) return '';

  const pairs = searchString.slice(1).split('&');

  const filteredSearch = pairs.reduce((paramsMap, pair) => {
    const [key, value] = pair.split('=');
    if (key && leadinQueryParamsKeys.indexOf(key) === -1) {
      paramsMap[key] = value;
    }
    return paramsMap;
  }, {});

  return $.param(filteredSearch);
}

export function serializeQueryObject(queryParamObject) {
  const queryKeys = Object.keys(queryParamObject);
  if (!queryKeys.length) {
    return '';
  }

  return queryKeys
    .map(
      key =>
        `${key}=${encodeURIComponent(JSON.stringify(queryParamObject[key]))}`
    )
    .join('&');
}
