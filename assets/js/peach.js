function ajax(data, url, callback) {
    var json = JSON.stringify(data);
    $.ajax({
        data: { 'json': json }, url: url, type: 'post', dataType: 'json',
        success: function (res) {
            if (res.success != 1) {
                alert(res.message);
                return false;
            }

            callback(res);
        },
        error: function (res) {
            window.log = res.responseText;
            console.log("error: cannot connect " + url);
        }
    })
}

function number(input, digit=2) {
    return commaSeparateNumber(input.eRound(digit));
}

function commaSeparateNumber(val) {
    nStr = val;
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

Number.prototype.eRound = function (decimals) {
    var y = this * 1;
    y = (Math.abs(y) > 0.000000001) ? y : 0;
    if (decimals === 0) {
        decimals = 0;
    } else {
        decimals = (decimals) ? decimals : 2;
    }
    var d = decimals * 1 + 8;
    d = (d > 12) ? 12 : d;
    y = Number(Math.round((y).toFixed(d) + 'e' + d) + 'e-' + d).toFixed(d);
    return Number(Math.round((y + "").replace(/[^0-9.-]/g, '') + 'e' + decimals) + 'e-' + decimals).toFixed(decimals);
};

String.prototype.eRound = function (decimals) {
    var y = this * 1;
    y = (Math.abs(y) > 0.000000001) ? y : 0;
    if (decimals === 0) {
        decimals = 0;
    } else {
        decimals = (decimals) ? decimals : 2;
    }
    var d = decimals * 1 + 8;
    d = (d > 12) ? 12 : d;
    y = Number(Math.round((y + "").replace(/[^0-9.-]/g, '') + 'e' + d) + 'e-' + d).toFixed(d);
    return Number(Math.round((y + "").replace(/[^0-9.-]/g, '') + 'e' + decimals) + 'e-' + decimals).toFixed(decimals);
};

function padStr(input, digit = 2, chr = '0') {
    return (chr + input).slice(-digit);
}

function date(format = '', set_date = '') {
    var date = set_date ? new Date(set_date) : new Date();
    if (isNaN(date.getTime())) return false;

    var d = padStr(date.getDate()), m = padStr(date.getMonth() + 1), y = date.getFullYear();
    var h = padStr(date.getHours()), i = padStr(date.getMinutes()), s = padStr(date.getSeconds());
    var t = new Date(y, m, 0).getDate();
    var str = ''

    if (format === '') {
        str = `${y}-${m}-${d}`;
    } else if (format === 'now') {
        str = `${y}-${m}-${d} ${h}:${i}:${s}`;
    } else if (format === 'time') {
        str = date.getTime();
    } else {
        str = format.replace('y', y).replace('m', m).replace('d', d).replace('h', h).replace('i', i).replace('s', s).replace('t', t);
    }
    return str;
}