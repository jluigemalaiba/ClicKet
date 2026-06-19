<section class="staff-grid-two" data-subsection="create">
  <article class="staff-card">
    <div class="staff-card-heading">
      <div>
        <p>News Management</p>
        <h2>Create news article</h2>
      </div>
      <span>Draft-first workflow</span>
    </div>
    <form class="staff-form-grid" action="#" method="post">
      <label>
        <span>Headline</span>
        <input type="text" placeholder="Article headline">
      </label>
      <label>
        <span>Category</span>
        <select>
          <option>Event Guide</option>
          <option>Venue Advisory</option>
          <option>Payment Reminder</option>
          <option>Platform News</option>
        </select>
      </label>
      <label>
        <span>Cover Image Upload</span>
        <input type="file" accept="image/*">
      </label>
      <label>
        <span>Status</span>
        <select>
          <option>Draft</option>
          <option>Published</option>
          <option>Archived</option>
        </select>
      </label>
      <label class="staff-field-span">
        <span>Rich Text Editor</span>
        <textarea rows="8" placeholder="Write article content"></textarea>
      </label>
      <label class="staff-check-row">
        <input type="checkbox">
        <span>Featured news</span>
      </label>
      <div class="staff-form-actions">
        <button class="staff-secondary-btn" type="button">Save Draft</button>
        <button class="staff-action-btn" type="button"><?= $isAdmin ? 'Publish' : 'Submit for Review' ?></button>
      </div>
    </form>
  </article>

  <article class="staff-card" data-subsection="editor">
    <div class="staff-card-heading">
      <div>
        <p>Publishing Workflow</p>
        <h2>Draft, publish, archive, feature</h2>
      </div>
    </div>
    <div class="staff-editor-toolbar">
      <?php foreach (['B', 'I', 'H1', 'Link', 'Image', 'Quote', 'List'] as $tool): ?>
        <button type="button"><?= sp_h($tool) ?></button>
      <?php endforeach; ?>
    </div>
    <div class="staff-news-preview">
      <span>Cover Preview</span>
      <strong>Venue entry policy refresh</strong>
      <small>Structured article preview with status and featured placement controls.</small>
    </div>
    <div class="staff-card-actions">
      <button type="button">Preview Article</button>
      <button type="button" <?= $isAdmin ? '' : 'disabled' ?>>Archive News</button>
      <button type="button" <?= $isAdmin ? '' : 'disabled' ?>>Toggle Featured</button>
    </div>
  </article>
</section>

<section class="staff-section" data-subsection="publish">
  <div class="staff-section-heading">
    <div>
      <p>Article Library</p>
      <h2>Draft, published, archived, and featured news</h2>
    </div>
  </div>
  <div class="staff-table-wrap">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Article</th>
          <th>Author</th>
          <th>Status</th>
          <th>Featured</th>
          <th>Updated</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($payload['news'] as $article): ?>
          <tr data-search-row>
            <td><strong><?= sp_h($article['title']) ?></strong></td>
            <td><?= sp_h($article['author']) ?></td>
            <td><span class="staff-status <?= sp_status_class($article['status']) ?>"><?= sp_h($article['status']) ?></span></td>
            <td><?= sp_h($article['featured']) ?></td>
            <td><?= sp_h($article['updated']) ?></td>
            <td><button type="button">Edit</button><button type="button" <?= $isAdmin ? '' : 'disabled' ?>>Publish</button><button type="button" <?= $isAdmin ? '' : 'disabled' ?>>Archive</button></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
