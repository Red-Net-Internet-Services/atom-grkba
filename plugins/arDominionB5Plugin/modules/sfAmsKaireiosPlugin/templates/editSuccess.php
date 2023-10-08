<?php decorate_with('layout_2col.php'); ?>
<?php use_helper('Date'); ?>

<?php slot('sidebar'); ?>

  <?php include_component('repository', 'contextMenu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <?php echo get_component('informationobject', 'descriptionHeader', ['resource' => $resource, 'title' => (string) $grkba, 'hideLevelOfDescription' => true]); ?>

  <?php if (isset($sf_request->source)) { ?>
    <div class="alert alert-info" role="alert">
      <?php echo __('This is a duplicate of record %1%', ['%1%' => $sourceInformationObjectLabel]); ?>
    </div>
  <?php } ?>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'informationobject', 'action' => 'edit']), ['id' => 'editForm']); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'informationobject', 'action' => 'add']), ['id' => 'editForm']); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="elements-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#elements-collapse" aria-expanded="false" aria-controls="elements-collapse">
            <?php echo __('Context'); ?>
          </button>
        </h2>
        <div id="elements-collapse" class="accordion-collapse collapse" aria-labelledby="elements-heading">
          <div class="accordion-body">
            <?php echo render_field($form->identifier
                ->help(__('The unambiguous reference code used to uniquely identify this resource.'))
                ->label(__('Identifier').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
            ); ?>

            <?php echo get_partial(
                'informationobject/identifierOptions',
                ['mask' => $mask, 'hideAltIdButton' => true]
            ); ?>

            <?php echo render_field($form->title
                ->help(__('The name given to this resource.'))
                ->label(__('Title').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource); ?>

            <?php echo get_partial('dcNames', $sf_data->getRaw('dcNamesComponent')->getVarHolder()->getAll()); ?>

            <?php echo get_partial('dcDates', $sf_data->getRaw('dcDatesComponent')->getVarHolder()->getAll()); ?>

            <?php echo render_field($form->scopeAndContent
                ->help(__('An abstract, table of contents or description of the resource\'s scope and contents.'))
                ->label(__('Description')), $resource); ?>

            <?php
                $taxonomy = QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID);
                $taxonomyUrl = url_for([$taxonomy, 'module' => 'taxonomy']);
                $extraInputs = '<input class="list" type="hidden" value="'
                    .url_for(['module' => 'term', 'action' => 'autocomplete', 'taxonomy' => $taxonomyUrl])
                    .'">';
                if (QubitAcl::check($taxonomy, 'createTerm')) {
                    $extraInputs .= '<input class="add" type="hidden" data-link-existing="true" value="'
                        .url_for(['module' => 'term', 'action' => 'add', 'taxonomy' => $taxonomyUrl])
                        .' #name">';
                }
                echo render_field(
                    $form->subjectAccessPoints->label(__('Subject'))->help(__(
                        'The topic of the resource. Search for an existing term in the Subject taxonomy'
                        .' by typing the first few characters of the term name. Alternatively, type a new'
                        .' name to create and link to a new subject term.'
                    )),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>

            <?php echo render_field($form->type
                ->help(__('<p>The nature or genre of the resource.</p><p>Assign as many types as applicable. The <em>Type</em> options are limited to the DCMI Type vocabulary.</p><p>Assign the <em>Collection</em> value if this resource is the top-level for a set of lower-level (child) resources.</p><p>Please note: if this resource is linked to a digital object, the <em>image</em>, <em>text</em>, <em>sound</em> or <em>moving image</em> types are added automatically upon output, so do not duplicate those values here.</p>'))
            ); ?>



            <?php echo render_field($form->extentAndMedium
                ->help(__('<p>The file format, physical medium, or dimensions of the resource.</p><p>Please note: if this resource is linked to a digital object, the Internet Media Types (MIME) will be added automatically upon output, so don\'t duplicate those values here.</p>'))
                ->label(__('Format')), $resource); ?>

            <?php echo render_field($form->locationOfOriginals
                ->help(__('Related material(s) from which this resource is derived.'))
                ->label(__('Source')), $resource); ?>

            <?php echo render_field(
                $form->language->help(__('Language(s) of this resource.')),
                null,
                ['class' => 'form-autocomplete']
            ); ?>

            <?php echo render_field(
                $form->repository
                    ->label(
                        __('Relation (Archival Institution)')
                        .' <span class="form-required" title="'
                        .__(
                            'This is a mandatory element for this resource or one of its'
                            .' higher descriptive levels (if part of a collection hierarchy).'
                        )
                        .'">*</span>'
                    )
                    ->help(__(
                        '<p>The name of the organization which has custody of the resource.</p>'
                        .'<p>Search for an existing name in the organization records by typing the'
                        .' first few characters of the name. Alternatively, type a new name to create'
                        .' and link to a new organization record.</p>'
                    )),
                null,
                [
                    'class' => 'form-autocomplete',
                    'extraInputs' => '<input class="list" type="hidden" value="'
                        .url_for($sf_data->getRaw('repoAcParams'))
                        .'"><input class="add" type="hidden" data-link-existing="true" value="'
                        .url_for(['module' => 'repository', 'action' => 'add'])
                        .' #authorizedFormOfName">',
                ]
            ); ?>

            <?php
                $taxonomy = QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID);
                $taxonomyUrl = url_for([$taxonomy, 'module' => 'taxonomy']);
                $extraInputs = '<input class="list" type="hidden" value="'
                    .url_for(['module' => 'term', 'action' => 'autocomplete', 'taxonomy' => $taxonomyUrl])
                    .'">';
                if (QubitAcl::check($taxonomy, 'createTerm')) {
                    $extraInputs .= '<input class="add" type="hidden" data-link-existing="true" value="'
                        .url_for(['module' => 'term', 'action' => 'add', 'taxonomy' => $taxonomyUrl])
                        .' #name">';
                }
                echo render_field(
                    $form->placeAccessPoints->label(__('Coverage'))->help(__(
                        '<p>The name of a place or geographic area which is a topic of the resource'
                        .' or relevant to its jurisdiction.</p><p>Search for an existing term in the'
                        .' Place taxonomy by typing the first few characters of the place name.'
                        .' Alternatively, type a new name to create and link to a new place.</p><p>Please'
                        .' note: if you entered a place of creation, publication or contribution that will'
                        .' be output automatically, so don’t repeat that place name here.</p>'
                    )),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>

            <?php echo render_field($form->accessConditions
                ->help(__('General Information about the context, the information object etc.'))
                ->label(__('Notes')), $resource); ?>
            <?php echo render_field($form->number
                ->help(__('General Information about the context, the information object etc.'))
                ->label(__('Number')), $grkba); ?>

            <?php
                $taxonomy = QubitTaxonomy::getById(QubitTaxonomy::GRKBA_AMS_KEYWORD_ID);
                $taxonomyUrl = url_for([$taxonomy, 'module' => 'taxonomy']);
                $extraInputs = '<input class="list" type="hidden" value="'
                    .url_for(['module' => 'term', 'action' => 'autocomplete', 'taxonomy' => $taxonomyUrl])
                    .'">';
                if (QubitAcl::check($taxonomy, 'createTerm')) {
                    $extraInputs .= '<input class="add" type="hidden" data-link-existing="true" value="'
                        .url_for(['module' => 'term', 'action' => 'add', 'taxonomy' => $taxonomyUrl])
                        .' #name">';
                }
                echo render_field(
                    $form->keywords->label(__('Keywords'))->help(__(
                        '<p>Keywords</p>'
                    )),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>

            
          </div>
        </div>
      </div>
      <?php echo get_partial('informationobject/adminInfo', ['form' => $form, 'resource' => $resource]); ?>
    </div>

    <?php echo get_partial('informationobject/editActions', ['resource' => (null !== $parent ? $parent : $resource)]); ?>

  </form>

<?php end_slot(); ?>
