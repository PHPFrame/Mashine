<div id="eb-client"></div>

<script>
(function (w, $) {

var EbClient = function (selector, user, pass) {
  var self = this;

  self.user = user;
  self.pass = pass;
  self.url = base_url + 'eb';
  self.container = $(selector);
  self.initUI();

  self.api('GET', 'heartbeat', function (er, data) {
    if (!data || !data.api_version) {
      alert('Could not connect to eb API');
    }

    self.getStats();
    self.getBackups();
  });
};

EbClient.prototype.initUI = function () {
  var self = this;

  self.ui = {
    stats: $('<div>'),
    backups: $('<div>')
  };

  self.ui.stats.evently({
    _init: function () {
      //console.log('hahaha');
    },
    data: {
      mustache: '<h2>Site stats</h2>' +
                '<p>Disk usage: {{disk_usage}}<br />Db size: {{db_size}}</p>',
      data: function (e, data) {
        //console.log(data);
        return data;
      }
    }
  });

  self.ui.backups.evently({
    data: {
      mustache: '<h2>Backups on server</h2>' +
                '<p>' +
                '<button id="create-backup">Create backup</button> ' +
                '<button id="restore-backup-from-file">Restore from local file</button>' +
                '</p>' +
                '<table>' +
                '<tr><th>Created</th><th>File size</th><th></th></tr>' +
                '{{#backups}}<tr>' +
                '<td>{{ctime}}</td><td>{{size}}</td>' +
                '<td style="text-align:right;">' +
                '<button id="download-backup" value="{{id}}">Download</button> ' +
                '<button id="restore-backup" value="{{id}}">Restore</button> ' +
                '<button id="delete-backup" value="{{id}}">Delete</button>' +
                '</td>' +
                '</tr>{{/backups}}' +
                '</table>',
      data: function (e, data) {
        var i, len = data.length, curr;

        for (i = 0; i < len; i++) {
          curr = data[i];
          curr.ctime = (new Date(curr.ctime*1000)).toNiceString();
          curr.size = curr.size.bytesToHuman();
        }

        return { backups: data };
      },
      selectors: {
        '#create-backup': {
          click: function (e) {
            self.api('POST', 'backups/' + (+new Date()), function (er, data) {
              if (er) {}
              //console.log(data);
              self.getBackups();
            });
          }
        },
        '#download-backup': {
          click: function (e) {
            w.open(self.url + '/backups/' + this.value, this.value);
          }
        },
        '#delete-backup': {
          click: function (e) {
            self.api('DELETE', 'backups/' + this.value, function (er, data) {
              if (er) {}
              //console.log(data);
              self.getBackups();
            });
          }
        },
        '#restore-backup': {},
        '#restore-backup-from-file': {}
      }
    }
  });

  self.container.append(self.ui.stats);
  self.container.append(self.ui.backups);
};

EbClient.prototype.getStats = function () {
  var self = this;

  self.api('GET', 'stats', function (er, data) {
    //console.log(data);
    self.ui.stats.trigger('data', [ data ]);
  });
};

EbClient.prototype.getBackups = function () {
  var self = this;

  self.api('GET', 'backups', function (er, data) {
    self.ui.backups.trigger('data', [ data ]);
  });
};

EbClient.prototype.api = function (method, resource, params, cb) {
  var self = this, url = self.url + '/' + resource;

  if (typeof params === 'function') {
    cb = params;
    params = null;
  }

  if (!params) {
    params = {};
  }

  params.suppress_response_codes = 1;
  url += '?' + $.param(params);

  $.ajax({
    url:  url,
    username: self.user,
    password: self.pass,
    type: method,
    success: function (data) {
      cb(null, data);
    },
    error: function () {},
    complete: function () {}
  });
};

w.createEbClient = function (selector, user, pass) {
  return new EbClient(selector, user, pass);
};

})(window, jQuery);


jQuery(document).ready(function () {
  var ebClient = createEbClient('#eb-client', 'eb', 'W1ckedFl4n');
  //console.log(ebClient);
});
</script>
