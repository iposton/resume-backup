<?php require __DIR__ . '/content.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= escapeContent($content['site']['title']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/styles.css?v=13">
  <script src="resume-chat.js?v=5" defer></script>
  <script src="main.js?v=10" defer></script>
</head>
<body>
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
  <header class="mdl-layout__header">
    <div class="mdl-layout__header-row">
      <a class="mdl-layout-title" href="<?= contentUrl($content['site']['home_url']) ?>"><?= escapeContent($content['site']['header_heading']) ?></a>
    <div class="mdl-layout__tab-bar mdl-js-ripple-effect" aria-label="<?= escapeContent($content['resume']['navigation_label']) ?>">
      <?php foreach (['about', 'resume', 'contact'] as $tab): ?>
      <a href="#scroll-tab-<?= $tab ?>" class="mdl-layout__tab<?= $tab === 'about' ? ' is-active' : '' ?>"><?= escapeContent($content['site']['tabs'][$tab]) ?></a>
      <?php endforeach; ?>
    </div>
    </div>
  </header>
  <?php $status = $_GET['success'] ?? null; if ($status === '1' || $status === '-1'): ?>
  <div id="div1" role="<?= $status === '1' ? 'status' : 'alert' ?>" class="form-messages <?= $status === '1' ? 'success' : 'error' ?>"><?= escapeContent($content['contact'][$status === '1' ? 'success' : 'error']) ?></div>
  <?php endif; ?>
  <main class="mdl-layout__content">
    <section class="mdl-layout__tab-panel is-active" id="scroll-tab-about">
      <div class="page-content"><div class="mdl-grid"><div class="container mdl-shadow--4dp">
        <a class="profile-avatar" href="<?= contentUrl($content['site']['home_url']) ?>"><img src="<?= contentUrl($content['site']['avatar']['url']) ?>" alt="<?= escapeContent($content['site']['avatar']['alt']) ?>"></a>
        <h2><?= escapeContent($content['site']['heading']) ?></h2>
        <?php $accordionIndex = 0; foreach ($content['about']['blocks'] as $block): ?>
          <?php if ($block['type'] === 'paragraph'): ?>
          <p><?= escapeContent($block['text']) ?></p>
          <?php elseif ($block['type'] === 'accordion'): $accordionIndex++; ?>
          <div id="accordion<?= $accordionIndex === 1 ? '' : $accordionIndex ?>">
            <h3><?= escapeContent($block['title']) ?></h3>
            <div><ul>
              <?php foreach ($block['items'] as $item): ?>
              <li><?php renderParts($item); ?></li>
              <?php endforeach; ?>
            </ul></div>
          </div>
          <?php if ($accordionIndex < 3): ?><br><?php endif; ?>
          <?php endif; ?>
        <?php endforeach; ?>
        <section class="ian-chat" aria-labelledby="ian-chat-title">
          <h3 id="ian-chat-title"><?= escapeContent($content['chat']['title']) ?></h3>
          <p class="ian-chat-description"><?= escapeContent($content['chat']['description']) ?></p>
          <div class="ian-chat-examples">
            <?php foreach ($content['chat']['examples'] as $example): ?>
            <button type="button" class="ian-chat-example"><?= escapeContent($example) ?></button>
            <?php endforeach; ?>
          </div>
          <div id="ian-chat-log" class="ian-chat-log" role="log" aria-live="polite" aria-relevant="additions" tabindex="0">
            <p class="ian-chat-answer"><?= escapeContent($content['chat']['welcome']) ?></p>
          </div>
          <form id="ian-chat-form">
            <label for="ian-chat-question"><?= escapeContent($content['chat']['label']) ?></label>
            <div class="ian-chat-controls">
              <input id="ian-chat-question" type="text" maxlength="500" required placeholder="<?= escapeContent($content['chat']['placeholder']) ?>" autocomplete="off">
              <button class="mdl-button" type="submit"><?= escapeContent($content['chat']['submit']) ?></button>
            </div>
          </form>
          <noscript>Enable JavaScript to ask questions, or read the Resume tab.</noscript>
        </section>
        <script id="ian-chat-data" type="application/json"><?= json_encode(['resume' => $content['resume'], 'about' => $content['about'], 'chat' => $content['chat']], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?></script>
      </div></div></div>
    </section>
    <section class="mdl-layout__tab-panel" id="scroll-tab-resume">
      <div class="page-content" id="about"><div class="mdl-grid">
        <nav class="section-nav" aria-label="<?= escapeContent($content['resume']['navigation_label']) ?>">
          <p class="section-nav-title"><?= escapeContent($content['resume']['navigation_title']) ?></p>
          <ul>
            <?php foreach (['about', 'work', 'edu', 'skills', 'refs'] as $id): ?>
            <li><a class="smooth" href="#<?= $id ?>"><?= escapeContent($content['resume']['labels'][$id]) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </nav>
        <div class="container mdl-shadow--4dp res-container">
          <section>
            <header><h3><?= escapeContent($content['resume']['heading']) ?></h3></header>
            <div><p><?= escapeContent($content['resume']['intro']) ?></p><br></div>
          </section>
          <section id="work">
            <header><h3><?= escapeContent($content['resume']['labels']['work']) ?></h3></header>
            <div>
              <?php foreach ($content['resume']['jobs'] as $job): ?>
              <h4><?php renderParts($job['heading']); ?></h4>
              <h5><?= escapeContent($job['dates']) ?></h5>
              <ul>
                <?php foreach ($job['bullets'] as $bullet): ?>
                <li><?php renderParts($bullet); ?></li>
                <?php endforeach; ?>
              </ul>
              <?php endforeach; ?>
            </div>
          </section>
          <section id="edu">
            <header><h3><?= escapeContent($content['resume']['labels']['edu']) ?></h3></header>
            <div>
              <?php foreach ($content['resume']['education'] as $school): ?>
              <h4><?= escapeContent($school['heading']) ?></h4>
              <h5><?= escapeContent($school['dates']) ?></h5>
              <p><?= escapeContent($school['description']) ?></p>
              <?php endforeach; ?>
            </div>
          </section>
          <section id="skills">
            <header class="skills-title"><h3><?= escapeContent($content['resume']['labels']['skills']) ?></h3></header>
            <p class="skills-caption"><?= escapeContent($content['resume']['skills_caption']) ?></p>
            <div class="mdl-grid skills-zone">
              <?php foreach ($content['resume']['skills'] as $group): ?>
              <div class="skill-card">
                <h4><?= escapeContent($group['title']) ?></h4>
                <ul class="skill-list">
                  <?php foreach ($group['items'] as $skill):
                    $rating = $skill['proficiency'] ?? null;
                    $rating = is_numeric($rating) ? max(0, min(100, (float) $rating)) : null;
                    $color = preg_match('/^#[0-9a-f]{6}$/i', $skill['color'] ?? '') ? $skill['color'] : '#475569';
                  ?>
                  <li class="skill-item">
                    <div class="skill-labels">
                      <span class="skill-pill" style="--skill-color: <?= escapeContent($color) ?>"><?= escapeContent($skill['name']) ?></span>
                      <span class="skill-rating"><?= $rating === null ? escapeContent($content['resume']['skills_unrated']) : escapeContent((string) $rating) . '%' ?></span>
                    </div>
                    <?php if ($rating !== null): ?>
                    <div class="skill-meter" role="meter" aria-label="<?= escapeContent($skill['name']) ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $rating ?>" aria-valuetext="<?= $rating ?>% self-assessed proficiency">
                      <span style="width: <?= $rating ?>%"></span>
                    </div>
                    <?php else: ?>
                    <div class="skill-meter is-unrated" aria-hidden="true"></div>
                    <?php endif; ?>
                  </li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <?php endforeach; ?>
            </div>
          </section>
          <section id="refs">
            <header><h3><?= escapeContent($content['resume']['labels']['refs']) ?></h3></header>
            <hr><div>
              <?php foreach ($content['resume']['references'] as $reference): ?>
              <h6><?= escapeContent($reference['author']) ?></h6>
              <blockquote><?= escapeContent($reference['quote']) ?></blockquote><hr>
              <?php endforeach; ?>
            </div>
          </section>
          <div id="print-tooltip" class="pdf-print">
            <a href="<?= contentUrl($content['resume']['download']['url']) ?>" download><?= escapeContent($content['resume']['download']['label']) ?></a>
          </div>
        </div>
      </div></div>
    </section>
    <section class="mdl-layout__tab-panel" id="scroll-tab-contact">
      <div class="page-content"><div class="mdl-grid"><div class="container mdl-shadow--4dp">
        <form action="mailer.php" method="post" id="_form_1029" accept-charset="utf-8">
          <div class="mdl-grid">
            <?php foreach (['name', 'email', 'message'] as $field): ?>
            <div class="mdl-textfield mdl-cell">
              <label class="mdl-textfield__label" for="<?= $field ?>"><?= escapeContent($content['contact']['labels'][$field]) ?></label>
              <?php if ($field === 'message'): ?>
              <textarea required class="mdl-textfield__input" id="message" name="message" rows="5" maxlength="5000"></textarea>
              <?php else: ?>
              <input class="mdl-textfield__input" type="<?= $field === 'email' ? 'email' : 'text' ?>" required autocomplete="<?= $field ?>" id="<?= $field ?>" name="<?= $field ?>"<?= $field === 'name' ? ' maxlength="100"' : '' ?>>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <div id="egghead">
              <label for="website"><?= escapeContent($content['contact']['honeypot_label']) ?></label>
              <input type="text" name="egghead" id="website" tabindex="-1" autocomplete="off">
            </div>
            <button class="mdl-button" type="submit" data-submit-label="<?= escapeContent($content['contact']['submit']) ?>" data-sending-label="<?= escapeContent($content['contact']['sending']) ?>">
              <span><?= escapeContent($content['contact']['submit']) ?></span>
            </button>
          </div>
        </form>
      </div></div></div>
    </section>
    <div class="mdl-layout-spacer"></div>
    <footer class="mdl-mini-footer">
      <div class="mdl-mini-footer--right-section">
        <?php foreach ($content['site']['social'] as $social): ?>
        <a class="social-btn social-btn__<?= escapeContent($social['icon']) ?>" href="<?= contentUrl($social['url']) ?>">
          <span class="visuallyhidden"><?= escapeContent($social['label']) ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </footer>
  </main>
  <a href="<?= contentUrl($content['site']['email']['url']) ?>" aria-label="<?= escapeContent($content['site']['email']['label']) ?>" id="email-me" class="mdl-button"><span aria-hidden="true">✉</span></a>
</div>
</body>
</html>
