document.getElementById("find_command").addEventListener("click", function (e) {
    e.preventDefault();
    f = document.getElementById('f_find_command');
    console.log(toJSONString(f));
});

document.getElementById("claim_team").addEventListener("click", function (e) {
    e.preventDefault();
    f = document.getElementById('f_claim_team');
    console.log(toJSONString(f));
});

document.getElementById("open_league").addEventListener("click", function (e) {
    e.preventDefault();
    f = document.getElementById('f_open_league');
    console.log(toJSONString(f));
});

function toJSONString(form) {
    var obj = {};
    var elements = form.querySelectorAll("input, select, textarea");
    for (var i = 0; i < elements.length; ++i) {
        var element = elements[i];
        var name = element.name;
        var value = element.value;
        //console.log(element.type);
        if (name) {
            if(element.type == 'checkbox'){
                if( element.checked) {
                    obj[name] = value;
                }
            }else {
                obj[name] = value;
            }
        }
    }

    fbq('track', 'SendMail' , obj);

    var xhr = new XMLHttpRequest();
    var url = "http://api.mygame4u.com/mail/lending";
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var json = JSON.parse(xhr.responseText);
            if(json.answer == true){
                //console.log(json.answer);
                location.reload();
            };
        }
    };
    var data = JSON.stringify(obj);
    xhr.send(data);

}