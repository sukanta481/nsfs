/* OpsDesk shared JS (no build step, ES5-friendly enough for old browsers on office PCs). */
(function () {
  var OPS = window.OPS = {};

  OPS.toast = function (msg, isErr) {
    var t = document.getElementById('opsToast');
    if (!t) return;
    t.textContent = msg;
    t.className = 'ops-toast show' + (isErr ? ' err' : '');
    clearTimeout(t._h);
    t._h = setTimeout(function () { t.className = 'ops-toast'; }, isErr ? 4000 : 2200);
  };

  /** POST to ops_api.php; resolves with JSON {ok, ...} or rejects with an Error(message). */
  OPS.api = function (action, data) {
    var body = new URLSearchParams();
    body.append('action', action);
    body.append('csrf', window.OPS_CSRF);
    Object.keys(data || {}).forEach(function (k) { body.append(k, data[k] == null ? '' : data[k]); });
    return fetch('ops_api.php', { method: 'POST', body: body, credentials: 'same-origin', headers: { 'X-Requested-With': 'fetch' } })
      .then(function (r) { return r.text().then(function (txt) { return { status: r.status, txt: txt }; }); })
      .then(function (res) {
        var j;
        try { j = JSON.parse(res.txt); } catch (e) { throw new Error(res.status === 200 ? 'Unexpected reply. Are you still logged in?' : 'Server error (' + res.status + ')'); }
        if (!j.ok) throw new Error(j.error || 'Something went wrong');
        return j;
      });
  };

  OPS.open = function (html) {
    document.getElementById('opsModalBox').innerHTML = html;
    document.getElementById('opsModal').className = 'ops-modal-bg show';
    var first = document.querySelector('#opsModalBox input:not([type=hidden]), #opsModalBox select, #opsModalBox textarea');
    if (first) first.focus();
  };
  OPS.close = function () { document.getElementById('opsModal').className = 'ops-modal-bg'; };
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') OPS.close(); });

  OPS.esc = function (s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
  };

  /** Ask for a reason, then post a reversal. kind: 'hand' | 'advance' */
  OPS.reverse = function (kind, id, label) {
    OPS.open(
      '<div class="card-h"><h3><i class="fa fa-rotate-left"></i> Reverse entry</h3></div>' +
      '<div class="card-b"><p style="margin-top:0">' + OPS.esc(label) + '</p>' +
      '<div class="note info" style="margin-bottom:12px"><i class="fa fa-circle-info"></i><div>The original entry stays in the passbook, crossed out. A new reversal entry cancels its amount.</div></div>' +
      '<div class="field"><label>Reason (required)</label><input class="input" id="revReason" placeholder="e.g. Typed wrong amount"></div></div>' +
      '<div class="foot"><button class="btn" onclick="OPS.close()">Cancel</button><button class="btn primary" id="revGo">Reverse entry</button></div>'
    );
    document.getElementById('revGo').onclick = function () {
      var reason = document.getElementById('revReason').value.trim();
      if (!reason) { OPS.toast('Please give a reason', true); return; }
      this.disabled = true;
      OPS.api(kind + '_reverse', { id: id, reason: reason })
        .then(function () { location.reload(); })
        .catch(function (e) { OPS.toast(e.message, true); document.getElementById('revGo').disabled = false; });
    };
  };
})();
