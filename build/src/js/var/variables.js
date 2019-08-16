define(function() {
    return {
        requestUrl : M.cfg.wwwroot + '/report/elucidsitereport/request_handler.php',
        requestType : 'GET',
        requestDataType : 'json',
        whiteColor : "rgba(255, 255, 255, 0.8)",
        todaysActivityBlock : "#todaysactivityblock .ct-chart",
        activeUsersBlock : "#activeusersblock .ct-chart",
        courseProgressBlock : "#courseprogressblock .ct-chart",
        lpStatsBlock : "#lpstatsblock .ct-chart",
        month_1 : "JAN",
        month_2 : "FEB",
        month_3 : "MAR",
        month_4 : "APR",
        month_5 : "MAY",
        month_6 : "JUN",
        month_7 : "JUL",
        month_8 : "AUG",
        month_9 : "SEP",
        month_10 : "OCT",
        month_11 : "NOV",
        month_12 : "DEC",
        clock12_0 : "12:00 AM",
        clock12_1 : "01:00 AM",
        clock12_2 : "02:00 AM",
        clock12_3 : "03:00 AM",
        clock12_4 : "04:00 AM",
        clock12_5 : "05:00 AM",
        clock12_6 : "06:00 AM",
        clock12_7 : "07:00 AM",
        clock12_8 : "08:00 AM",
        clock12_9 : "09:00 AM",
        clock12_10 : "10:00 AM",
        clock12_11 : "11:00 AM",
        clock12_12 : "12:00 PM",
        clock12_13 : "01:00 PM",
        clock12_14 : "02:00 PM",
        clock12_15 : "03:00 PM",
        clock12_16 : "04:00 PM",
        clock12_17 : "05:00 PM",
        clock12_18 : "06:00 PM",
        clock12_19 : "07:00 PM",
        clock12_20 : "08:00 PM",
        clock12_21 : "09:00 PM",
        clock12_22 : "10:00 PM",
        clock12_23 : "11:00 PM",

        changeExportUrl : function (filter, exportUrlLink) {
            $(exportUrlLink).each(function() {
                var oldUrl = $(this)[0].href;
                $(this)[0].href = oldUrl.replace(/filter=(.*)/, "filter="+filter);
            });
        },

        // Function to get the details from the URL
        getUrlParameter : function(sParam) {
            var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

            for (i = 0; i < sURLVariables.length; i++) {
                sParameterName = sURLVariables[i].split('=');

                if (sParameterName[0] === sParam) {
                    return sParameterName[1] === undefined ? true : sParameterName[1];
                }
            }
        },

        /* Format Date in specific format */
        formatDate : function (date, format, utc) {
            var MMMM = ["\x00", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            var MMM = ["\x01", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            var dddd = ["\x02", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
            var ddd = ["\x03", "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

            function ii(i, len) {
                var s = i + "";
                len = len || 2;
                while (s.length < len) s = "0" + s;
                return s;
            }

            var y = utc ? date.getUTCFullYear() : date.getFullYear();
            format = format.replace(/(^|[^\\])yyyy+/g, "$1" + y);
            format = format.replace(/(^|[^\\])yy/g, "$1" + y.toString().substr(2, 2));
            format = format.replace(/(^|[^\\])y/g, "$1" + y);

            var M = (utc ? date.getUTCMonth() : date.getMonth()) + 1;
            format = format.replace(/(^|[^\\])MMMM+/g, "$1" + MMMM[0]);
            format = format.replace(/(^|[^\\])MMM/g, "$1" + MMM[0]);
            format = format.replace(/(^|[^\\])MM/g, "$1" + ii(M));
            format = format.replace(/(^|[^\\])M/g, "$1" + M);

            var d = utc ? date.getUTCDate() : date.getDate();
            format = format.replace(/(^|[^\\])dddd+/g, "$1" + dddd[0]);
            format = format.replace(/(^|[^\\])ddd/g, "$1" + ddd[0]);
            format = format.replace(/(^|[^\\])dd/g, "$1" + ii(d));
            format = format.replace(/(^|[^\\])d/g, "$1" + d);

            var H = utc ? date.getUTCHours() : date.getHours();
            format = format.replace(/(^|[^\\])HH+/g, "$1" + ii(H));
            format = format.replace(/(^|[^\\])H/g, "$1" + H);

            var h = H > 12 ? H - 12 : H == 0 ? 12 : H;
            format = format.replace(/(^|[^\\])hh+/g, "$1" + ii(h));
            format = format.replace(/(^|[^\\])h/g, "$1" + h);

            var m = utc ? date.getUTCMinutes() : date.getMinutes();
            format = format.replace(/(^|[^\\])mm+/g, "$1" + ii(m));
            format = format.replace(/(^|[^\\])m/g, "$1" + m);

            var s = utc ? date.getUTCSeconds() : date.getSeconds();
            format = format.replace(/(^|[^\\])ss+/g, "$1" + ii(s));
            format = format.replace(/(^|[^\\])s/g, "$1" + s);

            var f = utc ? date.getUTCMilliseconds() : date.getMilliseconds();
            format = format.replace(/(^|[^\\])fff+/g, "$1" + ii(f, 3));
            f = Math.round(f / 10);
            format = format.replace(/(^|[^\\])ff/g, "$1" + ii(f));
            f = Math.round(f / 10);
            format = format.replace(/(^|[^\\])f/g, "$1" + f);

            var T = H < 12 ? "AM" : "PM";
            format = format.replace(/(^|[^\\])TT+/g, "$1" + T);
            format = format.replace(/(^|[^\\])T/g, "$1" + T.charAt(0));

            var t = T.toLowerCase();
            format = format.replace(/(^|[^\\])tt+/g, "$1" + t);
            format = format.replace(/(^|[^\\])t/g, "$1" + t.charAt(0));

            var tz = -date.getTimezoneOffset();
            var K = utc || !tz ? "Z" : tz > 0 ? "+" : "-";
            if (!utc) {
                tz = Math.abs(tz);
                var tzHrs = Math.floor(tz / 60);
                var tzMin = tz % 60;
                K += ii(tzHrs) + ":" + ii(tzMin);
            }
            format = format.replace(/(^|[^\\])K/g, "$1" + K);

            var day = (utc ? date.getUTCDay() : date.getDay()) + 1;
            format = format.replace(new RegExp(dddd[0], "g"), dddd[day]);
            format = format.replace(new RegExp(ddd[0], "g"), ddd[day]);

            format = format.replace(new RegExp(MMMM[0], "g"), MMMM[M]);
            format = format.replace(new RegExp(MMM[0], "g"), MMM[M]);

            format = format.replace(/\\(.)/g, "$1");

            return format;
        }
    }
});