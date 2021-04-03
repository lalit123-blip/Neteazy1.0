import { initInterframe } from '../lib/Interframe';
import { backgroundIframeUrl, iframeUrl } from '../constants/leadinConfig';

function createIframeElement(iframeSrc) {
  const iframe = document.createElement('iframe');
  iframe.id = 'leadin-iframe';
  iframe.src = iframeSrc;
  return iframe;
}

export function createIframe() {
  const iframe = createIframeElement(iframeUrl);
  initInterframe(iframe);
  document.getElementById('leadin-iframe-container').appendChild(iframe);
}

export function createBackgroundIframe() {
  const iframe = createIframeElement(backgroundIframeUrl);
  iframe.style.display = 'none';
  initInterframe(iframe);
  document.body.appendChild(iframe);
}
