// Ajax loading function

function loadstopsim() {
  // set an ajax variable for XML request
  var xmlhttp = new XMLHttpRequest();
  // once everything is ready and good
  xmlhttp.onreadystatechange = function(){
    if (xmlhttp.readyState==4 && xmlhttp.status==200)
      {
      // get the element, and set to response text
      document.getElementById("run").innerHTML=xmlhttp.responseText;
      }
    }
  xmlhttp.open("GET","../public/txt/stopsim.txt",true);
  xmlhttp.send();
}
