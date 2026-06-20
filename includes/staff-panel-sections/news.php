<?php
$newsCategories = ['For Fans', 'For Organizers', 'Platform Updates'];
$articles = $payload['news'] ?? [];
$published = array_values(array_filter($articles, static fn (array $article): bool => strtolower((string) ($article['status'] ?? '')) === 'published'));
usort($published, static fn (array $a, array $b): int => strcmp((string) ($b['published_at'] ?? ''), (string) ($a['published_at'] ?? '')));
$featuredArticleId = $published[0]['id'] ?? '';
?>

<section class="staff-news-workspace" data-subsection="create">
  <header class="staff-news-workspace__head">
    <div><p>News management</p><h2>Build a clear update, then publish when it is ready.</h2><span>Drafts and archived posts stay internal. The newest published post is automatically featured.</span></div>
    <div class="staff-news-workspace__status"><strong><?= sp_count(count($published)) ?></strong><span>live articles</span></div>
  </header>

  <form class="staff-news-builder" data-news-form enctype="multipart/form-data">
    <section class="staff-news-builder__main">
      <div class="staff-news-builder__section-head"><div><p>Article basics</p><h3>Start with the main story</h3></div><span>Required</span></div>
      <div class="staff-news-fields">
        <label class="staff-news-field--wide"><span>Headline</span><input name="title" type="text" maxlength="130" placeholder="Give the update a clear, useful title" required></label>
        <label><span>Category</span><select name="category" required><?php foreach ($newsCategories as $category): ?><option><?= sp_h($category) ?></option><?php endforeach; ?></select></label>
        <label><span>Visibility</span><select name="status" required><option value="Published">Published</option><option value="Draft">Draft</option></select><small>Published articles appear on the public News and Featured News sections.</small></label>
        <label class="staff-news-field--full staff-news-banner-field"><span>Banner image <b>16:9 rectangle</b></span><input name="banner" type="file" accept="image/jpeg,image/png,.jpg,.jpeg,.png" data-news-banner-input><small>JPG or PNG, minimum 1200 × 675 px. A banner is required before publishing.</small><div class="staff-news-banner-preview" data-news-banner-preview hidden><img src="" alt="Banner preview"><span>16:9 banner preview</span></div></label>
        <label class="staff-news-field--full"><span>Main description</span><textarea name="description" rows="4" maxlength="360" placeholder="Write a concise introduction that gives readers the important context." required></textarea><small>This appears under the headline in the public News feed once published.</small></label>
      </div>
    </section>

    <aside class="staff-news-builder__rail" data-subsection="editor">
      <p>Publishing guide</p><h3>Useful, not noisy.</h3>
      <ol><li>Choose who needs the update.</li><li>State the main change in the description.</li><li>Break details into readable sections.</li><li>Only publish when it is ready for everyone.</li></ol>
      <div class="staff-news-auto-feature"><span>Auto featured</span><strong>Newest published article</strong><small>No manual featured toggle needed.</small></div>
    </aside>

    <section class="staff-news-builder__sections">
      <div class="staff-news-builder__section-head"><div><p>Article sections</p><h3>Build the full story</h3></div><button class="staff-secondary-btn" type="button" data-add-news-section>+ Add section</button></div>
      <div data-news-sections>
        <article class="staff-news-section-editor" data-news-section>
          <div class="staff-news-section-editor__number">01</div>
          <div><label><span>Section header</span><input name="section_header[]" type="text" placeholder="What changed" required></label><label><span>Section content</span><textarea name="section_content[]" rows="5" placeholder="Explain the detail in a way readers can scan." required></textarea></label></div>
          <button type="button" class="staff-news-remove" data-remove-news-section aria-label="Remove section">×</button>
        </article>
      </div>
      <template id="staffNewsSectionTemplate"><article class="staff-news-section-editor" data-news-section><div class="staff-news-section-editor__number"></div><div><label><span>Section header</span><input name="section_header[]" type="text" placeholder="Add a section heading" required></label><label><span>Section content</span><textarea name="section_content[]" rows="5" placeholder="Write the details for this section." required></textarea></label></div><button type="button" class="staff-news-remove" data-remove-news-section aria-label="Remove section">×</button></article></template>
    </section>

    <footer class="staff-news-builder__footer"><p data-news-form-message>Only articles with a Published status appear on the public News page, with their publish date and time.</p><div><button class="staff-action-btn" type="submit">Save article</button></div></footer>
  </form>
</section>

<section class="staff-section" data-subsection="publish">
  <div class="staff-section-heading"><div><p>Article library</p><h2>Published, draft, and archived articles</h2></div><span><?= sp_count(count($articles)) ?> total</span></div>
  <div class="staff-table-wrap"><table class="staff-table staff-news-library"><thead><tr><th>Article</th><th>Audience</th><th>Status</th><th>Published</th><th>Updated</th></tr></thead><tbody>
    <?php foreach ($articles as $article): ?>
      <?php $isFeatured = ($article['id'] ?? '') === $featuredArticleId; ?>
      <tr data-search-row><td><strong><?= sp_h($article['title'] ?? 'Untitled article') ?></strong><small><?= $isFeatured ? 'Featured automatically · newest published' : sp_h($article['description'] ?? '') ?></small></td><td><?= sp_h($article['category'] ?? '') ?></td><td><span class="staff-status <?= sp_status_class($article['status'] ?? 'Draft') ?>"><?= sp_h($article['status'] ?? 'Draft') ?></span></td><td><?= !empty($article['published_at']) ? sp_h(clicketNewsDate((string) $article['published_at'])) : '—' ?></td><td><?= sp_h(clicketNewsDate((string) ($article['updated_at'] ?? $article['created_at'] ?? ''))) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$articles): ?><tr><td colspan="5">No articles yet. Create the first update above.</td></tr><?php endif; ?>
  </tbody></table></div>
</section>
