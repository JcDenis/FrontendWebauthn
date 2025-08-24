/*global dotclear */
'use strict';

dotclear.ready(() => {
  // DOM ready and content loaded

  const fwData = dotclear.getData('FrontendWebauthn');
  dotclear.fwData = fwData;

  // webauthn passkey authentication
  dotclear.webauthn = (action, url) => {
    // (A) HELPER FUNCTIONS
    const fwHelper = {
      // (A1) ARRAY BUFFER TO BASE 64
      atb: (b) => {
        const u = new Uint8Array(b);
        let s = '';
        for (let i = 0; i < u.byteLength; i++) {
          s += String.fromCharCode(u[i]);
        }
        return btoa(s);
      },

      // (A2) BASE 64 TO ARRAY BUFFER
      bta: (o) => {
        const pre = '=?BINARY?B?';
        const suf = '?=';
        for (const k in o) {
          if (typeof o[k] === 'string') {
            const s = o[k];
            if (s.startsWith(pre) && s.endsWith(suf)) {
              const b = window.atob(s.substring(pre.length, s.length - suf.length));
              const u = new Uint8Array(b.length);
              for (let i = 0; i < b.length; i++) {
                u[i] = b.charCodeAt(i);
              }
              o[k] = u.buffer;
            }
          } else {
            fwHelper.bta(o[k]);
          }
        }
      },
      ajax : (url, data, after) => {
        let form = new FormData();
        for (let [k,v] of Object.entries(data)) { form.append(k,v); }
        fetch(url, { method: "POST", body: form })
        .then(res => res.text())
        .then(res => after(res))
        .catch(err => { alert("ERROR!"); console.error(err); });
      },

      prepareAuthentication : () => fwHelper.ajax(url, {
        step : "prepareAuthentication",
        FrontendSessionaction : dotclear.fwData.action,
        FrontendSessioncheck : dotclear.fwData.check
      }, async (rsp) => {
        try {
          rsp = JSON.parse(rsp);
          fwHelper.bta(rsp.arguments);
          fwHelper.processAuthentication(await navigator.credentials.get(rsp.arguments));
        } catch (e) { alert(dotclear.fwData.err + ' (a1)'); console.error(e); }
      }),

      processAuthentication : cred => fwHelper.ajax(url, {
        step : "processAuthentication",
        FrontendSessionaction : dotclear.fwData.action,
        FrontendSessioncheck : dotclear.fwData.check,
        id: cred.rawId ? fwHelper.atb(cred.rawId) : null,
        client: cred.response.clientDataJSON ? fwHelper.atb(cred.response.clientDataJSON) : null,
        authenticator: cred.response.authenticatorData ? fwHelper.atb(cred.response.authenticatorData) : null,
        signature: cred.response.signature ? fwHelper.atb(cred.response.signature) : null,
        user: cred.response.userHandle ? fwHelper.atb(cred.response.userHandle) : null,
        transport : cred.response.getTransports ? cred.response.getTransports() : null
      }, rsp => {
        rsp = JSON.parse(rsp);
        if ((rsp.message || 'ko') === 'ok') {
          // on success, reload page to get user session from rest service
          window.location.reload();
        } else {
          window.alert(rsp.message || dotclear.fwData.err + ' (a2)');
        }
      }),

      prepareRegistration : () => fwHelper.ajax(url, {
        step : "prepareRegistration",
        FrontendSessionaction : dotclear.fwData.action,
        FrontendSessioncheck : dotclear.fwData.check
      }, async (rsp) => {
        try {
          rsp = JSON.parse(rsp);
          fwHelper.bta(rsp.arguments);
          fwHelper.processRegistration(await navigator.credentials.create(rsp.arguments));
        } catch (e) { alert(dotclear.fwData.err + ' (r1)'); console.error(e); }
      }),

      processRegistration : cred => fwHelper.ajax(url, {
        step : "processRegistration",
        FrontendSessionaction : dotclear.fwData.action,
        FrontendSessioncheck : dotclear.fwData.check,
        client: cred.response.clientDataJSON ? fwHelper.atb(cred.response.clientDataJSON) : null,
        attestation: cred.response.attestationObject ? fwHelper.atb(cred.response.attestationObject) : null,
        transports: cred.response.getTransports ? fwHelper.atb(cred.response.getTransports()) : null
      }, rsp => {
        rsp = JSON.parse(rsp);
        if ((rsp.message || 'ko') === 'ok') {
          // on success, reload page to get user session from rest service
          window.location.reload();
        } else {
          window.alert(rsp.message || dotclear.fwData.err + ' (a2)');
        }
      }),
    };

    try {
      // browser does not support passkey
      if (!('credentials' in navigator)) {
        throw new Error('Browser not supported.');
      }

      if (action === 'authenticate') {
        fwHelper.prepareAuthentication();
      }

      if (action === 'register') {
        fwHelper.prepareRegistration();
      }

    } catch (error) {
      console.log(error.message || 'unknown error occured');
    }
  };

  if ('credentials' in navigator) {
    $('.webauthn_authenticate').show();
    $('.webauthn_register').show();

    var aurl = $('.webauthn_authenticate a').attr('href');
    var rurl = $('.webauthn_register a').attr('href');

    $('.webauthn_authenticate a').on('click', (e) => {
      dotclear.webauthn('authenticate', aurl);
      e.preventDefault();
      e.returnValue = '';
    });

    $('.webauthn_register a').on('click', (e) => {
      dotclear.webauthn('register', rurl);
      e.preventDefault();
      e.returnValue = '';
    });
  } else {
    $('.webauthn_authenticate').hide();
    $('.webauthn_register').hide();
  }
});