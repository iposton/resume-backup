'use strict';
// A deterministic resume matcher: no model, API calls, fees or conversation storage.
function answerResumeQuestion(question, data) {
  const q = question.toLowerCase().replace(/[’']/g, '').replace(/[^a-z0-9+#.\s]/g, ' ').replace(/\s+/g, ' ').trim();
  const partsText = (parts) => parts.map(part => part.text).join('').trim();
  const jobs = data.resume.jobs;
  const describeJob = job => `${partsText(job.heading)} (${job.dates}).`;
  if (!q) return data.chat.empty;
  if (/\b(journey|background|story|started|start|became)\b/.test(q)) return data.chat.journey;
  const years = [...q.matchAll(/\b(?:19|20)\d{2}\b/g)].map(match => Number(match[0]));
  if (years.length && /\b(work|worked|working|job|jobs|employer|experience|company|companies|role|roles|where|doing)\b/.test(q)) {
    const matches = jobs.filter(job => years.some(year => year >= job.start_year && year <= (job.end_year ?? new Date().getFullYear())));
    return matches.length ? `Ian’s listed roles for ${[...new Set(years)].join(' and ')}:\n${matches.map(describeJob).join('\n')}` : `Ian’s resume does not list a role for ${years.join(' or ')}.`;
  }
  const employers = [['bowtie','bowtie'], ['frontiergeotek','bowtie'], ['tragic','tragic'], ['trivnow','trivnow'], ['the hq','the hq'], ['colab','colab']];
  const employer = employers.find(([alias]) => q.includes(alias));
  if (employer) {
    const job = jobs.find(job => partsText(job.heading).toLowerCase().includes(employer[1]));
    if (job) return `${describeJob(job)}\n${job.bullets.map(partsText).join('\n')}`;
  }
  if (/\b(project|projects|built|portfolio|apps|applications)\b/.test(q)) {
    const projects = data.about.blocks.find(block => block.type === 'accordion');
    return projects ? `Projects Ian built:\n${projects.items.map(item => item.map(part => part.url ? `${part.text}: ${part.url}` : part.text).join('')).join('\n')}` : data.chat.unknown;
  }
  if (/\b(education|school|study|studied|training|assembly|learned)\b/.test(q)) {
    return data.resume.education.map(school => `${school.heading} (${school.dates}). ${school.description}`).join('\n');
  }
  if (/\b(current|currently|now|today|present)\b/.test(q) && /\b(work|working|job|role|do|doing|employed|where)\b/.test(q)) {
    const job = jobs.find(job => job.end_year === null);
    return job ? `${describeJob(job)}\n${job.bullets.map(partsText).join('\n')}` : data.chat.unknown;
  }
  const skillItems = data.resume.skills.flatMap(group => group.items);
  const languageQuestion = /\b(language|languages)\b/.test(q);
  const rankingQuestion = /\b(top|best|strongest|highest|proficient|proficiency|strength|strengths)\b/.test(q);
  if (rankingQuestion && /\b(skill|skills|language|languages|tool|tools|strength|strengths|proficient|proficiency)\b/.test(q)) {
    const category = /\b(db|database|databases|sql)\b/.test(q) ? 'database'
      : /\bcloud\b/.test(q) ? 'cloud'
      : /\bai\b/.test(q) ? 'ai'
      : /\b(coding|programming|code|frontend|backend)\b/.test(q) ? 'coding' : null;
    const pool = skillItems.filter(item => (!category || item.categories?.includes(category))
      && (!languageQuestion || item.kind === 'language'));
    const ranked = pool.filter(item => typeof item.proficiency === 'number' && Number.isFinite(item.proficiency))
      .sort((a, b) => b.proficiency - a.proficiency);
    if (!ranked.length) return 'Ian hasn’t rated those skills yet.';
    const requestedCount = q.match(/\btop\s+(\d+)\b/);
    const top = requestedCount
      ? ranked.slice(0, Math.max(1, Math.min(Number(requestedCount[1]), ranked.length)))
      : ranked.filter(item => item.proficiency === ranked[0].proficiency);
    const names = top.map(item => item.name);
    const summary = names.length < 2 ? names[0] : `${names.slice(0, -1).join(', ')} and ${names.at(-1)}`;
    if (requestedCount) return `His top ${names.length} ${category || 'technical'} skills are ${summary}.`;
    if (category === 'database') return `${summary} ${names.length === 1 ? 'is his strongest database skill' : 'are his strongest database skills'}. He uses PostgreSQL in his backend and data-heavy workflow work at Bowtie.`;
    if (category === 'cloud') return `${summary} ${names.length === 1 ? 'is his strongest cloud area' : 'are his strongest cloud areas'}. Azure is part of his current backend and automation work at Bowtie.`;
    if (category === 'ai') return `${summary} ${names.length === 1 ? 'is his highest-rated AI tool' : 'are his highest-rated AI tools'}. He uses AI coding tools to help with planning, debugging and reviewing his work.`;
    return `${summary} are his strongest ${languageQuestion ? 'coding languages' : 'coding skills'}. He works with C#, .NET, Azure tools and Octopus Deploy, and also builds with React, Next.js, Supabase and Django.`;

  }
  if (languageQuestion) {
    return `Ian’s listed coding languages are ${skillItems.filter(item => item.kind === 'language').map(item => item.name).join(', ')}.`;
  }
  const skills = skillItems.map(item => item.name);
  const mentionedSkill = skills.find(skill => q.includes(skill.toLowerCase()));
  if (mentionedSkill && /\b(use|uses|used|know|experience|skill|skills|work|worked|with|does|can)\b/.test(q)) {
    return `${mentionedSkill} is listed among Ian’s skills. ${data.resume.intro}`;
  }
  if (/\b(skill|skills|stack|technology|technologies|tools|languages|frameworks)\b/.test(q)) {
    return `Ian’s listed skills include ${skills.join(', ')}.`;
  }
  if (/\b(experience|career|jobs|employers)\b/.test(q) || /where.*\b(work|worked|working)\b/.test(q)) {
    return `${data.resume.intro}\n${jobs.map(describeJob).join('\n')}`;
  }
  return data.chat.unknown;
}
if (typeof module !== 'undefined' && module.exports) module.exports = { answerResumeQuestion };
if (typeof document !== 'undefined') {
  const dataNode = document.getElementById('ian-chat-data');
  if (dataNode) {
    const data = JSON.parse(dataNode.textContent);
    const form = document.getElementById('ian-chat-form');
    const input = document.getElementById('ian-chat-question');
    const log = document.getElementById('ian-chat-log');
    const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
    let typingTimer;
    let finishTyping;
    motionPreference.addEventListener('change', () => {
      if (motionPreference.matches) finishTyping?.();
    });
    function typeAnswer(answer, text) {
      log.setAttribute('aria-busy', 'true');
      answer.classList.add('is-typing');
      finishTyping = () => {
        clearTimeout(typingTimer);
        answer.textContent = text;
        answer.classList.remove('is-typing');
        log.setAttribute('aria-busy', 'false');
        log.scrollTop = log.scrollHeight;
        finishTyping = null;
      };
      if (motionPreference.matches) { finishTyping(); return; }
      const words = text.match(/\S+\s*/g) || [text];
      let index = 0;
      function tick() {
        const followBottom = log.scrollHeight - log.scrollTop - log.clientHeight < 48;
        answer.textContent += words[index++];
        if (followBottom) log.scrollTop = log.scrollHeight;
        if (index < words.length) typingTimer = setTimeout(tick, 35);
        else {
          answer.classList.remove('is-typing');
          log.setAttribute('aria-busy', 'false');
          finishTyping = null;
        }
      }
      tick();
    }
    function ask(question) {
      if (!question.trim()) return;
      finishTyping?.();
      const user = document.createElement('p');
      user.className = 'ian-chat-question';
      user.textContent = `${data.chat.question_prefix}: ${question.trim()}`;
      const answer = document.createElement('p');
      answer.className = 'ian-chat-answer';
      const text = `${data.chat.answer_prefix}: ${answerResumeQuestion(question, data)}`;
      log.append(user, answer);
      // Keep the interface bounded; nothing is persisted or sent off-device.
      while (log.children.length > 20) log.firstElementChild.remove();
      log.scrollTop = log.scrollHeight;
      input.value = '';
      input.focus({ preventScroll: true });
      typeAnswer(answer, text);
    }
    form.addEventListener('submit', event => { event.preventDefault(); ask(input.value); });
    document.querySelectorAll('.ian-chat-example').forEach(button => {
      button.addEventListener('click', () => ask(button.textContent));
    });
  }
}
