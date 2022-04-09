<div style="display: grid; grid-template-columns: auto 400px auto; pointer-events: none">
  <div id="top" style="grid-column: 2; pointer-events: all; background: gainsboro;">
  <a href="../public"><img src="../public/img/logo.gif"
    onmouseover="this.src='../public/img/logohover.gif'"
    onmouseout="this.src='../public/img/logo.gif'"/></a>
  </div>
  <div id = "nav" style="grid-column: 2; pointer-events: all; background: gainsboro;">
    <ul class="nav nav-pills">
    <li>
      <div class="buttonContainer">
        <input id = "abstract" type="checkbox" class="checkbox" <?= htmlspecialchars($checked) ?>/>
        <span id = "abstract"></span>
      </div>
    </li>
    <li><button id="new" onclick="location.href='./newdata.php'">+Link</button></li>
    <li><button id="home" onclick="location.href='./index.php'">+Box</button></li>
    </ul>
    <p>
         <?= htmlspecialchars($notice) ?>
        <form id = "linkdata" action="mydata.php" method="post">
          <fieldset>
            <div class="form-group">
              <input autofocus class="form-con" id="link" name="link" placeholder="CellX -i> CellY" type="text"/>
            </div>
            <ul class="nav nav-pills">
              <li>
                <div class="form-group">
                  <button id = "delete" type="submit">Delete</button>
                </div>
              </li>
              <li>
                <div class="buttonContainer">
                  <input id = "sim" type="checkbox" class="checkbox" />
                  <span id = "timestop"></span>
                </div>
              </li>
              <li>
                <input id="fixins" class="slider" type="range" min="0.05" max = "0.8"
                value = <?= htmlspecialchars($fixed) ?> step=".05"/>
              </li>
            </ul>
          </fieldset>
        </form>
      </p>
    </div>
</div>

    <div id = "viz" style="position: absolute; width: 100vw; height: 100vh; z-index: -1; top: 0; left: 0">
      <script src="../public/js/force.js"></script>
    </div>

    <div id = "sefue">
      <ul class="nav nav-pills">
        <li>
          TTYL:
          <br/>
          <button id="logout" onclick="location.href='./logout.php'">Log Out</button>
        </li>
      </ul>
    </div>
    <br/>
