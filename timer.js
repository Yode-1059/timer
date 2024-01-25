(function () {
  var timer;
  function updateDisplay() {
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var second = now.getSeconds();
    var formattedMinutes = minutes < 10 ? "0" + minutes : minutes;
    var formattedHours = hours < 10 ? "0" + hours : hours;

    document.getElementById("custom_timer").textContent =
      formattedHours + ":" + formattedMinutes;

    var remindTime = document
      .querySelector(`#remind_time`)
      .textContent.split(":");
    let remindHour = Number(remindTime[0]);
    let remindeMin = Number(remindTime[1]);

    if (second == 0 && hours == remindHour && minutes == remindeMin) {
      var reminder = document.getElementById("reminder").value;
      alert(`${reminder}の時間になりました`);
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    // 現在のURLを取得
    var currentUrl = window.location.href;
    // URLが 'index.php' で終わるかどうかをチェック
    if (currentUrl.endsWith("index.php")) {
      var newReminder = document.querySelector(
        `#${userData.user_login}_dashboard_reminder`
      ).value;
      var newTime = document.querySelector(
        `#${userData.user_login}_dashboard_timer`
      ).value;
      console.log(userData, newReminder, newTime);

      document.getElementById("remind_info").innerHTML =
        "現在の時刻<span id='custom_timer'>00:00</span> - " +
        newReminder +
        'が<span id="remind_time">' +
        newTime +
        "</span>にあります";
    }
  });

  function startClock() {
    timer = setInterval(function () {
      updateDisplay();
    }, 1000);
  }

  document.addEventListener("DOMContentLoaded", function () {
    startClock();

    document
      .querySelector("#dashbord_reminder")
      .addEventListener("submit", function (e) {
        e.preventDefault();

        // 新しいリマインドと時間を読み取る
        var newReminder = document.querySelector(
          `#${userData.user_login}_dashboard_reminder`
        ).value;
        var newTime = document.querySelector(
          `#${userData.user_login}_dashboard_timer`
        ).value;

        // 確認のためコンソールに表示
        console.log("Reminder:", newReminder);
        console.log("Time:", newTime);
        console.log(userData.user_login);

        // オプション情報を更新
        var xhr = new XMLHttpRequest();
        xhr.open("POST", ajaxurl, true);
        xhr.setRequestHeader(
          "Content-Type",
          "application/x-www-form-urlencoded; charset=UTF-8"
        );

        xhr.onload = function () {
          if (xhr.status >= 200 && xhr.status < 400) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
              document.getElementById("remind_info").innerHTML =
                "現在の時刻<span id='custom_timer'>00:00</span> - " +
                newReminder +
                "が" +
                newTime +
                "にあります";
              alert("予定が更新されました！");
            } else {
              alert("エラーが発生しました。");
            }
          }
        };

        xhr.send(
          "action=update_timer_options" +
            "&reminder=" +
            encodeURIComponent(newReminder) +
            "&time=" +
            encodeURIComponent(newTime) +
            "&nonce=" +
            encodeURIComponent(
              document.querySelector(
                `#${userData.user_login}_dashboard_reminder_nonce`
              ).value
            )
        );
      });
  });
})();
