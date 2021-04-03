import { serializeQueryObject } from '../utils/queryParams';
import { makeProxyRequest } from './wordpressApiClient';

export function fetchForms(searchQuery = '', offset = 0, limit = 10) {
  const payload = {
    offset,
    limit,
    formTypes: ['HUBSPOT'],
  };

  if (searchQuery) {
    payload.name__contains = searchQuery;
  }

  const queryParams = serializeQueryObject(payload);
  const formsPath = `/forms/v2/forms?${queryParams}`;

  return makeProxyRequest('get', formsPath).then(forms => {
    const filteredForms = [];

    forms.forEach(currentForm => {
      const { guid, name } = currentForm;
      filteredForms.push({ name, guid });
    });

    return filteredForms;
  });
}
