<script src="../public/js/uploadtype.js"></script>
<div id="top">
  <a href="../"><img src="../public/img/logohover.gif"
    onmouseover="this.src='../public/img/logo.gif'"
    onmouseout="this.src='../public/img/logo.gif'"/></a>
  </div>

  <div id = "nav">
    <ul class="nav nav-pills">
      <li>
        <div class="buttonContainer">
          <input id = "abstract" type="checkbox" class="checkbox" <?= htmlspecialchars($checked) ?>/>
          <span id = "abstract"></span>
        </div>
      </li>
      <li><button onclick="location.href='newdata.php'">+Link</button></li>
      <li><button onclick="location.href='index.php'">+Box</button></li>
    </ul>
    <p>
      <?= htmlspecialchars($message) ?>
      <form id = "groupfiledata" action="share.php" method="post">
        <fieldset>
          <div class="form-group">
            <input autofocus class="form-con" id="groupfile" name="groupfile" placeholder="Your Boxes" type="text"/>
          </div>
          <ul class="nav nav-pills">
            <li>
              <div class="form-group">
                <button id = "upload" type="submit">Share</button>
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
    </div>
  </p>

    <div id = "viz">
      <script src="../public/js/force.js"></script>
    </div>

    <div id = "sefue">
      <ul class="nav nav-pills">
        <li>
          TTYL:
          <br/>
      <button id="logout" onclick="location.href='logout.php'">Log Out</button>
        </li>
      </ul>
    </div>
    <br/>
