import React from 'react';
import UISelect from '../../UIComponents/UISelect';
import GutenbergWrapper from '../../Common/GutenbergWrapper';
import { i18n } from '../../../constants/leadinConfig';

export default function FormSelector({
  defaultOptions,
  loadOptions,
  onChange,
  value,
}) {
  return (
    <GutenbergWrapper>
      <p data-test-id="leadin-form-select">
        <b>{i18n.selectExistingForm}</b>
      </p>
      <UISelect
        defaultOptions={defaultOptions}
        cacheOptions={true}
        loadOptions={loadOptions}
        onChange={onChange}
        placeholder={i18n.selectForm}
        value={value}
      />
    </GutenbergWrapper>
  );
}
