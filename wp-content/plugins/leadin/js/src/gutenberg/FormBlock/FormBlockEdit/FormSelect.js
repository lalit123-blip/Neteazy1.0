import React, { useState } from 'react';
import debounce from 'lodash/debounce';
import { searchForms } from '../../../api/hubspotPluginApi';
import { fetchForms as searchFormsOAuth } from '../../../api/hubspotApiClient';
import useForm from './useForm';
import FormSelector from './FormSelector';
import FormErrorHandler from './FormErrorHandler';
import LoadingBlock from '../../Common/LoadingBlock';
import { oauth } from '../../../constants/leadinConfig';

const mapForm = form => ({
  label: form.name,
  value: form.guid,
});

export default function FormSelect({ formId, formName, handleChange }) {
  const { form, loading } = useForm(formId, formName);
  const [searchformError, setSearchFormError] = useState(null);

  const searchFormMethod = oauth ? searchFormsOAuth : searchForms;

  const loadOptions = debounce(
    (search, callback) => {
      searchFormMethod(search)
        .then(forms => callback(forms.map(mapForm)))
        .catch(error => setSearchFormError(error));
    },
    300,
    { trailing: true }
  );

  const defaultOptions = form && !oauth ? [mapForm(form)] : true;
  const value = form ? mapForm(form) : null;

  const formApiError = oauth && searchformError;
  return loading ? (
    <LoadingBlock />
  ) : !formApiError ? (
    <FormSelector
      defaultOptions={defaultOptions}
      loadOptions={loadOptions}
      onChange={handleChange}
      value={value}
    />
  ) : (
    <FormErrorHandler
      status={formApiError.status}
      resetErrorState={() => setSearchFormError(null)}
    />
  );
}
