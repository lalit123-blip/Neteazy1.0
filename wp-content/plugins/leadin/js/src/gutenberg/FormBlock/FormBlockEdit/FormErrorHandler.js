import React from 'react';
import UIButton from '../../UIComponents/UIButton';
import UIContainer from '../../UIComponents/UIContainer';
import GutenbergWrapper from '../../Common/GutenbergWrapper';
import { i18n, adminUrl, redirectNonce } from '../../../constants/leadinConfig';

function redirectToPlugin() {
  window.location.href = `${adminUrl}admin.php?page=leadin&leadin_expired=${redirectNonce}`;
}

export default function FormErrorHandler({ status, resetErrorState }) {
  const isUnauthorized = status === 401;
  const errorHeader = isUnauthorized
    ? i18n.unauthorizedHeader
    : i18n.formApiErrorHeader;
  const errorMessage = isUnauthorized
    ? i18n.unauthorizedMessage
    : i18n.formApiError;

  return (
    <GutenbergWrapper>
      <UIContainer textAlign="center">
        <h4>{errorHeader}</h4>
        <p>
          <b>{errorMessage}</b>
        </p>
        {isUnauthorized ? (
          <UIButton onClick={redirectToPlugin}>{i18n.goToPlugin}</UIButton>
        ) : (
          <UIButton onClick={resetErrorState}>{i18n.refreshForms}</UIButton>
        )}
      </UIContainer>
    </GutenbergWrapper>
  );
}
