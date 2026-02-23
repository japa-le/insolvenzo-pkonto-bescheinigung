<?php
/**
 * Plugin Name: Insolvenzo – Public Formular
 * Description: Öffentliches React-Formular - Bescheinigung Pfändungskonto mit PDF-Generierung
 * Version:     1.0.0
 * Author:      Janos
 * Text Domain: insolvenzo-form
 * 
 *  * Responsibilities:
 * - Register the Gutenberg block (dynamic block)
 * - Define the render_callback for frontend output
 * - Enqueue editor and frontend assets
 * - Serve as backend integration point for insolvency logic
 *
 * The actual form markup is rendered server-side.
 */

if (!defined('ABSPATH')) exit;

/**
 * Render a collapsible text info box.
 */
function insolvenzo_render_collapsible_text_box($title, $content, $is_open = false) {
    $title = (string) $title;
    $content = (string) $content;
    $is_open = (bool) $is_open;

    if ($title === '' && $content === '') {
        return;
    }

    $display_title = $title !== '' ? $title : __('Erklärung', 'insolvenzo-form');
    $box_class = $is_open ? 'insolvenzo-info-box insolvenzo-collapsible is-open' : 'insolvenzo-info-box insolvenzo-collapsible';
    ?>
    <div class="<?php echo esc_attr($box_class); ?>" data-insolvenzo-collapsible>
        <button type="button" class="insolvenzo-info-box-toggle" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>">
            <span class="insolvenzo-info-box-toggle-label"><?php echo esc_html($display_title); ?></span>
            <span class="insolvenzo-collapse-icon" aria-hidden="true"></span>
        </button>
        <div class="insolvenzo-info-box-panel"<?php echo $is_open ? '' : ' hidden'; ?>>
            <?php if ($content !== ''): ?>
                <div><?php echo wp_kses_post(nl2br($content)); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Render a collapsible video info box.
 */
function insolvenzo_render_collapsible_video_box($video_url, $is_open = false) {
    $video_url = (string) $video_url;
    $is_open = (bool) $is_open;

    if ($video_url === '') {
        return;
    }
    $box_class = $is_open ? 'insolvenzo-info-box insolvenzo-video-box insolvenzo-collapsible is-open' : 'insolvenzo-info-box insolvenzo-video-box insolvenzo-collapsible';
    ?>
    <div class="<?php echo esc_attr($box_class); ?>" data-insolvenzo-collapsible>
        <button type="button" class="insolvenzo-info-box-toggle" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>">
            <span class="insolvenzo-info-box-toggle-label"><?php esc_html_e('Erklärungsvideo', 'insolvenzo-form'); ?></span>
            <span class="insolvenzo-collapse-icon" aria-hidden="true"></span>
        </button>
        <div class="insolvenzo-info-box-panel"<?php echo $is_open ? '' : ' hidden'; ?>>
            <div class="insolvenzo-video-container">
                <video controls style="width: 100%; max-height: 300px;">
                    <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                    <?php esc_html_e('Ihr Browser unterstützt das Video-Tag nicht.', 'insolvenzo-form'); ?>
                </video>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render callback for the Insolvenzo form block
 */
function insolvenzo_form_render_callback($attributes, $content) {
    ob_start();
    ?>
    <div class="wp-block-insolvenzo-public-form insolvenzo-form">
        <!-- Horizontal Stepper -->
        <div class="insolvenzo-horizontal-stepper">
            <div class="insolvenzo-stepper-step" data-step="1">
                <div class="insolvenzo-stepper-circle">1</div>
                <div class="insolvenzo-stepper-label">Eingangsabfrage</div>
            </div>
            <div class="insolvenzo-stepper-line"></div>
            <div class="insolvenzo-stepper-step" data-step="2">
                <div class="insolvenzo-stepper-circle">2</div>
                <div class="insolvenzo-stepper-label">Kontoinhaber</div>
            </div>
            <div class="insolvenzo-stepper-line"></div>
            <div class="insolvenzo-stepper-step" data-step="3">
                <div class="insolvenzo-stepper-circle">3</div>
                <div class="insolvenzo-stepper-label">Pfändungsfreier Betrag</div>
            </div>
            <div class="insolvenzo-stepper-line"></div>
            <div class="insolvenzo-stepper-step" data-step="4">
                <div class="insolvenzo-stepper-circle">4</div>
                <div class="insolvenzo-stepper-label">Laufende Leistungen</div>
            </div>
            <div class="insolvenzo-stepper-line"></div>
            <div class="insolvenzo-stepper-step" data-step="5">
                <div class="insolvenzo-stepper-circle">5</div>
                <div class="insolvenzo-stepper-label">Einmalige Zahlungen</div>
            </div>
        </div>

        <form method="POST">
            <?php wp_nonce_field('insolvenzo_form_nonce'); ?>
            <?php wp_nonce_field('wp_rest', '_wpnonce_rest'); ?>

            <!-- Honeypot field (should stay empty) -->
            <div style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                <label for="website">Website</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off" />
            </div>

            <!-- Step 1: Eingangsabfrage -->
            <div class="insolvenzo-step" data-step-number="1">
              <h3><span class="insolvenzo-step-roman">I</span> Angaben zur Bescheinigung der bescheinigenden Person oder Stelle</h3>

              <div class="insolvenzo-step-two-columns">
                <div class="insolvenzo-step-left">

                  <div class="insolvenzo-form-group">
                    <label for="contact_email">E-Mail Kontaktadresse</label>
                    <input type="email" id="contact_email" name="contact_email" data-required />
                  </div>

                  <!-- Gemeinschaftskonto -->
                  <div class="insolvenzo-form-group">
                    <label>Handelt es sich bei dem Konto um ein Gemeinschaftskonto?</label>
                    <div class="insolvenzo-radio-group">
                      <label><input type="radio" name="gemeinschaftskonto" value="ja" data-required data-required-radio="gemeinschaftskonto" /> Ja</label>
                      <label><input type="radio" name="gemeinschaftskonto" value="nein" /> Nein</label>
                    </div>
                  </div>

                  <!-- Wer stellt die Bescheinigung aus? -->
                  <div class="insolvenzo-form-group">
                    <label for="issuer_type">Wer stellt diese Bescheinigung aus?</label>
                    <select id="issuer_type" name="issuer_type" data-required>
                      <option value="">Bitte wählen</option>
                      <option value="arbeitgeber">Arbeitgeber</option>
                      <option value="anwalt">Anwalt (ist eine geeignete Person)</option>
                      <option value="steuerberater">Steuerberater (ist eine geeignete Person)</option>
                      <option value="familienkasse">Familienkasse</option>
                      <option value="jobcenter">Jobcenter / Sozialamt</option>
                      <option value="rentenstelle">Rentenstelle</option>
                      <option value="sonstige_behoerde">Sonstige Behörde oder Stelle</option>
                      <option value="schuldnerberatung">Schuldnerberatung</option>
                    </select>
                  </div>

                  <!-- Adressfelder für alle AUSSER Schuldnerberatung (hat Firma) -->
                  <div class="insolvenzo-issuer-fields" data-issuer-fields="with-firma" style="display:none;">
                    <div class="insolvenzo-form-group">
                      <label for="issuer_firma">Firma</label>
                      <input type="text" id="issuer_firma" name="issuer_firma" data-required />
                    </div>
                    <div class="insolvenzo-form-group">
                      <label for="issuer_name">Name</label>
                      <input type="text" id="issuer_name" name="issuer_name" data-required />
                    </div>
                    <div class="insolvenzo-form-row">
                      <div class="insolvenzo-form-group" style="flex: 3;">
                        <label for="issuer_strasse">Straße</label>
                        <input type="text" id="issuer_strasse" name="issuer_strasse" data-required />
                      </div>
                      <div class="insolvenzo-form-group" style="flex: 1;">
                        <label for="issuer_hausnummer">Hausnummer</label>
                        <input type="text" id="issuer_hausnummer" name="issuer_hausnummer" data-required />
                      </div>
                    </div>
                    <div class="insolvenzo-form-row">
                      <div class="insolvenzo-form-group" style="flex: 1;">
                        <label for="issuer_plz">Postleitzahl</label>
                        <input type="text" id="issuer_plz" name="issuer_plz" data-required />
                      </div>
                      <div class="insolvenzo-form-group" style="flex: 2;">
                        <label for="issuer_ort">Ort</label>
                        <input type="text" id="issuer_ort" name="issuer_ort" data-required />
                      </div>
                    </div>
                    <div class="insolvenzo-form-group">
                      <label for="issuer_ansprech_vorname">Ansprechpartner:in – Vorname <em>(optional)</em></label>
                      <input type="text" id="issuer_ansprech_vorname" name="issuer_ansprech_vorname" />
                    </div>
                    <div class="insolvenzo-form-group">
                      <label for="issuer_ansprech_nachname">Ansprechpartner:in – Nachname <em>(optional)</em></label>
                      <input type="text" id="issuer_ansprech_nachname" name="issuer_ansprech_nachname" />
                    </div>
                    <div class="insolvenzo-form-group">
                      <label for="issuer_ansprech_funktion">Ansprechpartner:in – Funktion <em>(optional)</em></label>
                      <input type="text" id="issuer_ansprech_funktion" name="issuer_ansprech_funktion" />
                    </div>
                  </div>

                  <!-- Adressfelder nur für Schuldnerberatung (KEIN Firma-Feld) -->
                  <div class="insolvenzo-issuer-fields" data-issuer-fields="schuldnerberatung" style="display:none;">
                    <div class="insolvenzo-form-group">
                      <label for="sb_name">Name</label>
                      <input type="text" id="sb_name" name="sb_name" data-required />
                    </div>
                    <div class="insolvenzo-form-row">
                      <div class="insolvenzo-form-group" style="flex: 3;">
                        <label for="sb_strasse">Straße</label>
                        <input type="text" id="sb_strasse" name="sb_strasse" data-required />
                      </div>
                      <div class="insolvenzo-form-group" style="flex: 1;">
                        <label for="sb_hausnummer">Hausnummer</label>
                        <input type="text" id="sb_hausnummer" name="sb_hausnummer" data-required />
                      </div>
                    </div>
                    <div class="insolvenzo-form-row">
                      <div class="insolvenzo-form-group" style="flex: 1;">
                        <label for="sb_plz">Postleitzahl</label>
                        <input type="text" id="sb_plz" name="sb_plz" data-required />
                      </div>
                      <div class="insolvenzo-form-group" style="flex: 2;">
                        <label for="sb_ort">Ort</label>
                        <input type="text" id="sb_ort" name="sb_ort" data-required />
                      </div>
                    </div>
                    <div class="insolvenzo-form-group">
                      <label for="sb_ansprech_vorname">Ansprechpartner:in – Vorname <em>(optional)</em></label>
                      <input type="text" id="sb_ansprech_vorname" name="sb_ansprech_vorname" />
                    </div>
                    <div class="insolvenzo-form-group">
                      <label for="sb_ansprech_nachname">Ansprechpartner:in – Nachname <em>(optional)</em></label>
                      <input type="text" id="sb_ansprech_nachname" name="sb_ansprech_nachname" />
                    </div>
                    <div class="insolvenzo-form-group">
                      <label for="sb_ansprech_funktion">Ansprechpartner:in – Funktion <em>(optional)</em></label>
                      <input type="text" id="sb_ansprech_funktion" name="sb_ansprech_funktion" />
                    </div>
                  </div>

                </div>
                    <div class="insolvenzo-step-right">
                        <?php
                        $step1_text_title = isset($attributes['step1TextTitle']) ? $attributes['step1TextTitle'] : '';
                        $step1_text_content = isset($attributes['step1TextContent']) ? $attributes['step1TextContent'] : '';
                        $step1_video_url = isset($attributes['step1VideoUrl']) ? $attributes['step1VideoUrl'] : '';
                        $step1_has_text = !empty($step1_text_title) || !empty($step1_text_content);
                        $step1_has_video = !empty($step1_video_url);

                        insolvenzo_render_collapsible_text_box(
                            $step1_text_title,
                            $step1_text_content,
                            $step1_has_text
                        );
                        insolvenzo_render_collapsible_video_box($step1_video_url, ($step1_has_video && !$step1_has_text));
                        ?>
                </div>
              </div>
            </div>

            <!-- Step 2: Angaben zum Kontoinhaber und Pfändungsschutzkonto -->
            <div class="insolvenzo-step" data-step-number="2">
                <h3><span class="insolvenzo-step-roman">II</span> Angaben zum Kontoinhaber und Pfändungsschutzkonto</h3>
                <div class="insolvenzo-step-two-columns">
                    <div class="insolvenzo-step-left">
                        <!-- Angaben zum Kontoinhaber -->
                        <h4>Angaben zum Kontoinhaber</h4>
                        
                        <div class="insolvenzo-form-group">
                            <label for="account_holder_vorname">Vorname</label>
                            <input type="text" id="account_holder_vorname" name="account_holder_vorname" data-required />
                        </div>
                        
                        <div class="insolvenzo-form-group">
                            <label for="account_holder_nachname">Nachname</label>
                            <input type="text" id="account_holder_nachname" name="account_holder_nachname" data-required />
                        </div>
                        
                        <div class="insolvenzo-form-group">
                            <label for="account_holder_geburtsdatum">Geburtsdatum</label>
                            <input type="date" id="account_holder_geburtsdatum" name="account_holder_geburtsdatum" data-required />
                        </div>
                        
                        <div class="insolvenzo-form-row">
                            <div class="insolvenzo-form-group" style="flex: 3;">
                                <label for="account_holder_strasse">Straße</label>
                                <input type="text" id="account_holder_strasse" name="account_holder_strasse" data-required />
                            </div>
                            <div class="insolvenzo-form-group" style="flex: 1;">
                                <label for="account_holder_hausnummer">Hausnummer</label>
                                <input type="text" id="account_holder_hausnummer" name="account_holder_hausnummer" data-required />
                            </div>
                        </div>

                        <div class="insolvenzo-form-row">
                            <div class="insolvenzo-form-group" style="flex: 1;">
                                <label for="account_holder_plz">Postleitzahl</label>
                                <input type="text" id="account_holder_plz" name="account_holder_plz" data-required />
                            </div>
                            <div class="insolvenzo-form-group" style="flex: 2;">
                                <label for="account_holder_ort">Ort</label>
                                <input type="text" id="account_holder_ort" name="account_holder_ort" data-required />
                            </div>
                        </div>

                        <!-- Angaben zum Pfändungsschutzkonto -->
                        <h4>Angaben zum Pfändungsschutzkonto</h4>
                        
                        <div class="insolvenzo-form-group">
                            <label for="pkonto_kreditinstitut">Kreditinstitut</label>
                            <input type="text" id="pkonto_kreditinstitut" name="pkonto_kreditinstitut" data-required />
                        </div>
                        
                        <div class="insolvenzo-form-group">
                            <label for="pkonto_kontoangaben">Kontoangaben</label>
                            <select id="pkonto_kontoangaben" name="pkonto_kontoangaben" data-required>
                                <option value="">Bitte wählen</option>
                                <option value="kontonummer">Kontonummer</option>
                                <option value="iban">IBAN</option>
                                <option value="beide">Beides</option>
                            </select>
                        </div>
                        
                        <div class="insolvenzo-form-group pkonto-field pkonto-kontonummer">
                            <label for="pkonto_kontonummer">Kontonummer</label>
                            <input type="text" id="pkonto_kontonummer" name="pkonto_kontonummer" />
                        </div>
                        
                        <div class="insolvenzo-form-group pkonto-field pkonto-iban">
                            <label for="pkonto_iban">IBAN</label>
                            <input type="text" id="pkonto_iban" name="pkonto_iban" />
                        </div>
                    </div>
                    <div class="insolvenzo-step-right">
                        <?php
                        $step2_text_title = isset($attributes['step2TextTitle']) ? $attributes['step2TextTitle'] : '';
                        $step2_text_content = isset($attributes['step2TextContent']) ? $attributes['step2TextContent'] : '';
                        $step2_video_url = isset($attributes['step2VideoUrl']) ? $attributes['step2VideoUrl'] : '';
                        $step2_has_text = !empty($step2_text_title) || !empty($step2_text_content);
                        $step2_has_video = !empty($step2_video_url);

                        insolvenzo_render_collapsible_text_box(
                            $step2_text_title,
                            $step2_text_content,
                            $step2_has_text
                        );
                        insolvenzo_render_collapsible_video_box($step2_video_url, ($step2_has_video && !$step2_has_text));
                        ?>
                    </div>
                </div>
            </div>

            <!-- Step 3: Ermittlung des pfändungsfreien Betrags -->
            <div class="insolvenzo-step" data-step-number="3">
                <h3><span class="insolvenzo-step-roman">III</span> Ermittlung des pfändungsfreien Betrags</h3>
                <div class="insolvenzo-step-two-columns">
                    <div class="insolvenzo-step-left">
                        
                        <!-- Grundfreibetrag -->
                        <h4>Grundfreibetrag</h4>
                        <p class="insolvenzo-info-text">Der Grundfreibetrag wird systemseitig gemäß der jeweils gültigen Pfändungstabelle automatisch gesetzt. Das Feld ist nicht bearbeitbar.</p>
                        
                        <div class="insolvenzo-form-group">
                            <label for="grundfreibetrag">Der Ihnen zustehende Grundfreibetrag beträgt</label>
                            <div class="insolvenzo-display-field">
                                <span id="grundfreibetrag"><?php echo isset($attributes['grundfreibetrag']) ? number_format($attributes['grundfreibetrag'], 2, ',', '.') : '1.499,99'; ?></span> €
                            </div>
                            <input
                                type="hidden"
                                name="grundfreibetrag"
                                value="<?php echo esc_attr(isset($attributes['grundfreibetrag']) ? number_format((float) $attributes['grundfreibetrag'], 2, '.', '') : '1499.99'); ?>"
                            />
                        </div>

                        <!-- Unterhalts-/leistungsberechtigte Personen -->
                        <h4>Unterhalts- / leistungsberechtigte Personen</h4>
                        
                        <div class="insolvenzo-form-group">
                            <label for="dependents_count">Anzahl unterhaltsberechtigter Personen</label>
                            <input type="number" id="dependents_count" name="dependents_count" min="0" max="5" value="0" data-required />
                            <p class="insolvenzo-info-text"><small>Maximal 5 unterhaltsberechtigte Personen können berücksichtigt werden.</small></p>
                        </div>

                        <div id="dependents_details_wrapper">
                            <!-- Details für jede Person werden hier hinzugefügt -->
                        </div>

                        <!-- Erhöhungsbetrag -->
                        <h4>Erhöhungsbetrag für unterhaltspflichtige Personen</h4>
                        <p class="insolvenzo-info-text">Der Erhöhungsbetrag wird automatisch anhand der Anzahl der berücksichtigten Personen berechnet.</p>
                        
                        <div class="insolvenzo-form-group">
                            <label>Berechnung</label>
                            <div class="insolvenzo-calculation-display">
                                <div class="insolvenzo-calc-row">
                                    <span>Anzahl der zu berücksichtigenden Personen:</span>
                                    <span id="considered_persons_count">0</span>
                                </div>
                                <div class="insolvenzo-calc-row">
                                    <span>Betrag für die 1. Person:</span>
                                    <span><?php echo isset($attributes['erstePersonBetrag']) ? number_format((float) $attributes['erstePersonBetrag'], 2, ',', '.') : '585,23'; ?> €</span>
                                </div>
                                <div class="insolvenzo-calc-row">
                                    <span>Betrag je weitere Person (ab 2.):</span>
                                    <span><?php echo isset($attributes['unterhaltspersonBetrag']) ? number_format((float) $attributes['unterhaltspersonBetrag'], 2, ',', '.') : '326,04'; ?> €</span>
                                </div>
                                <div class="insolvenzo-calc-row" style="border-top: 1px solid #ccc; padding-top: 8px; margin-top: 8px;">
                                    <span><strong>Erhöhungsbetrag:</strong></span>
                                    <span id="enhancement_amount"><strong>0,00 €</strong></span>
                                </div>
                            </div>
                            <input
                                type="hidden"
                                name="unterhaltsperson_betrag"
                                value="<?php echo esc_attr(isset($attributes['unterhaltspersonBetrag']) ? number_format((float) $attributes['unterhaltspersonBetrag'], 2, '.', '') : '326.04'); ?>"
                            />
                            <input
                                type="hidden"
                                name="erste_person_betrag"
                                value="<?php echo esc_attr(isset($attributes['erstePersonBetrag']) ? number_format((float) $attributes['erstePersonBetrag'], 2, '.', '') : '585.23'); ?>"
                            />
                        </div>

                        <!-- Nachweis liegt vor -->
                        <h4>Nachweis liegt vor in Form von</h4>
                        
                        <div class="insolvenzo-form-group">
                            <label for="evidence_type">Art des Nachweises</label>
                            <select id="evidence_type" name="evidence_type" data-required>
                                <option value="">Bitte wählen</option>
                                <option value="bescheid">Bescheid (z.B. Kindergeld, Sozialleistung, Rente)</option>
                                <option value="kontoauszug">Kontoauszug</option>
                                <option value="arbeitgeber">Arbeitgeberbescheinigung</option>
                                <option value="sonstiges">Sonstiger geeigneter Nachweis</option>
                            </select>
                        </div>

                        <div id="sonstiges_nachweis" class="insolvenzo-form-group" style="display: none;">
                            <label for="sonstiges_beschreibung">Bitte beschreiben Sie:</label>
                            <textarea id="sonstiges_beschreibung" name="sonstiges_beschreibung" rows="3" placeholder="Art des sonstigen geeigneten Nachweises..."></textarea>
                        </div>

                        <p class="insolvenzo-info-text"><strong>Hinweis:</strong> Der Nachweis ist der bescheinigenden Stelle zur Prüfung vorzulegen.</p>

                    </div>
                    <div class="insolvenzo-step-right">
                        <?php
                        $step3_text_title = isset($attributes['step3TextTitle']) ? $attributes['step3TextTitle'] : '';
                        $step3_text_content = isset($attributes['step3TextContent']) ? $attributes['step3TextContent'] : '';
                        $step3_video_url = isset($attributes['step3VideoUrl']) ? $attributes['step3VideoUrl'] : '';
                        $step3_has_text = !empty($step3_text_title) || !empty($step3_text_content);
                        $step3_has_video = !empty($step3_video_url);

                        insolvenzo_render_collapsible_text_box(
                            $step3_text_title,
                            $step3_text_content,
                            $step3_has_text
                        );
                        insolvenzo_render_collapsible_video_box($step3_video_url, ($step3_has_video && !$step3_has_text));
                        ?>
                    </div>
                </div>
            </div>

            <!-- Step 4: Laufende Geldleistungen -->
            <div class="insolvenzo-step" data-step-number="4">
                <h3><span class="insolvenzo-step-roman">IV</span> Laufende Geldleistungen</h3>
                <div class="insolvenzo-step-two-columns">
                    <div class="insolvenzo-step-left">

                        <!-- Laufende Geldleistungen -->
                        <h4>Laufende Geldleistungen</h4>
                        <p class="insolvenzo-info-text">Hier tragen Sie regelmäßige Zahlungen ein, die Sie monatlich erhalten und den Grundfreibetrag übersteigen. Tragen Sie hier nur Beträge ein, die als regelmäßige Leistung gezahlt werden (monatlich). Einmalige Zahlungen gehören nicht hierhin.</p>
                        
                        <div class="insolvenzo-form-group">
                            <fieldset>
                                <legend>Art der regelmäßigen Leistung</legend>
                                <div class="insolvenzo-checkbox-group">
                                    <label><input type="checkbox" name="ongoing_leistung" value="buergergeld" /> Bürgergeld (SGB II)</label>
                                    <label><input type="checkbox" name="ongoing_leistung" value="sozialhilfe" /> Sozialhilfe (SGB XII)</label>
                                    <label><input type="checkbox" name="ongoing_leistung" value="asylblg" /> Leistungen nach dem Asylbewerberleistungsgesetz (AsylbLG)</label>
                                    <label><input type="checkbox" name="ongoing_leistung" value="wohngeld" /> Wohngeld</label>
                                </div>
                                <div class="insolvenzo-form-group">
                                    <label><input type="checkbox" name="ongoing_leistung" value="sonstige" /> Sonstige laufende Leistung:</label>
                                    <input type="text" name="ongoing_leistung_sonstige" placeholder="Bitte angeben..." class="insolvenzo-indent-input" />
                                </div>
                            </fieldset>
                        </div>

                        <div class="insolvenzo-form-group">
                            <label for="ongoing_amount">Monatlicher Betrag (€)</label>
                            <input type="number" id="ongoing_amount" name="ongoing_amount" step="0.01" />
                        </div>

                        <!-- Zusätzliche Leistungen wegen Krankheit oder Behinderung -->
                        <h4>Zusätzliche Leistungen wegen Krankheit oder Behinderung</h4>
                        <p class="insolvenzo-info-text">Tragen Sie hier nur dann etwas ein, wenn in Ihrem Bescheid ein zusätzlicher Betrag ausdrücklich genannt ist.</p>
                        
                        <div class="insolvenzo-form-group">
                            <label for="mehrbedarf_type">Art des Mehrbedarfs</label>
                            <select id="mehrbedarf_type" name="mehrbedarf_type">
                                <option value="">Bitte wählen</option>
                                <option value="behinderung">Mehrbedarf wegen Behinderung</option>
                                <option value="krankheit">Mehrbedarf wegen Krankheit</option>
                                <option value="ernaehrung">Mehrbedarf wegen kostenaufwändiger Ernährung</option>
                                <option value="pflege">Pflegebedingter Mehrbedarf</option>
                                <option value="sonstig">Sonstiger Mehrbedarf</option>
                            </select>
                        </div>

                        <div id="mehrbedarf_sonstige" class="insolvenzo-form-group" style="display: none;">
                            <input type="text" name="mehrbedarf_sonstige" placeholder="Bitte angeben..." />
                        </div>

                        <div class="insolvenzo-form-group">
                            <label for="mehrbedarf_amount">Monatlicher Betrag (€)</label>
                            <input type="number" id="mehrbedarf_amount" name="mehrbedarf_amount" step="0.01" />
                        </div>

                        <!-- Art der laufenden Geldleistung (unpfändbare Bezüge) -->
                        <h4>Art der laufenden Geldleistung</h4>
                        <p class="insolvenzo-info-text">Hier können Sie regelmäßige monatliche Zahlungen eintragen, die Sie für sich selbst erhalten und die nicht normales Arbeitseinkommen sind. Hier dürfen nur unpfändbare Bezüge eingetragen werden.</p>
                        
                        <div class="insolvenzo-form-group">
                            <fieldset>
                                <legend>Unpfändbare Bezüge</legend>
                                <div class="insolvenzo-checkbox-group">
                                    <label><input type="checkbox" name="unpiable_leistung" value="entschaedigung" /> Entschädigungs- oder Ausgleichsleistung nach einem Gesetz</label>
                                    <label><input type="checkbox" name="unpiable_leistung" value="rente_schaden" /> Rente oder laufende Zahlung wegen eines besonderen Schadens</label>
                                </div>
                                <div class="insolvenzo-form-group">
                                    <label><input type="checkbox" name="unpiable_leistung" value="sonstige" /> Sonstige gesetzlich unpfändbare Leistung:</label>
                                    <input type="text" name="unpiable_leistung_sonstige" placeholder="Bitte angeben..." class="insolvenzo-indent-input" />
                                </div>
                            </fieldset>
                        </div>

                        <div class="insolvenzo-form-group">
                            <label for="unpiable_amount">Monatlicher Betrag (€)</label>
                            <input type="number" id="unpiable_amount" name="unpiable_amount" step="0.01" />
                        </div>

                        <div class="insolvenzo-form-group">
                            <label>Nachweis liegt vor in Form von</label>
                            <div class="insolvenzo-checkbox-group">
                                <label><input type="checkbox" name="unpiable_evidence" value="bescheid" /> Bescheid</label>
                                <label><input type="checkbox" name="unpiable_evidence" value="sonstig" /> Sonstiger geeigneter Nachweis</label>
                            </div>
                        </div>

                        <!-- Kindergeld -->
                        <h4>Kindergeld</h4>
                        <p class="insolvenzo-info-text">Für jedes Kind bitte einen Eintrag:</p>
                        
                        <div id="kindergeld_container">
                            <div class="insolvenzo-card insolvenzo-card-child">
                                <div class="insolvenzo-card-header">
                                    <h5>Kind 1</h5>
                                </div>
                                <div class="insolvenzo-card-content">
                                    <div class="insolvenzo-form-row">
                                        <div class="insolvenzo-form-group" style="flex: 1;">
                                            <label>Geburtsmonat</label>
                                            <select name="kindergeld[0][monat]">
                                                <option value="">-- Monat --</option>
                                                <option value="01">Januar</option>
                                                <option value="02">Februar</option>
                                                <option value="03">März</option>
                                                <option value="04">April</option>
                                                <option value="05">Mai</option>
                                                <option value="06">Juni</option>
                                                <option value="07">Juli</option>
                                                <option value="08">August</option>
                                                <option value="09">September</option>
                                                <option value="10">Oktober</option>
                                                <option value="11">November</option>
                                                <option value="12">Dezember</option>
                                            </select>
                                        </div>
                                        <div class="insolvenzo-form-group" style="flex: 1;">
                                            <label>Geburtsjahr</label>
                                            <input type="number" name="kindergeld[0][jahr]" min="1950" max="2030" placeholder="YYYY" />
                                        </div>
                                    </div>
                                    
                                    <div class="insolvenzo-form-group">
                                        <label>Monatlicher Betrag (€)</label>
                                        <input type="number" name="kindergeld[0][betrag]" step="0.01" placeholder="259,00 €" />
                                    </div>

                                    <div class="insolvenzo-form-group">
                                        <label>Nachweis liegt vor in Form von</label>
                                        <div class="insolvenzo-checkbox-group">
                                            <label><input type="checkbox" name="kindergeld[0][nachweis]" value="bescheid" /> Kindergeldbescheid</label>
                                            <label><input type="checkbox" name="kindergeld[0][nachweis]" value="lohnabr" /> Lohnabrechnung (bei Auszahlung über den Arbeitgeber)</label>
                                            <label><input type="checkbox" name="kindergeld[0][nachweis]" value="konto" /> Kontoauszug</label>
                                        </div>
                                        <p class="insolvenzo-info-text"><small>Bei Nachweis durch Kontoauszug muss der Betrag mit dem eingetragenen Betrag übereinstimmen.</small></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="insolvenzo-btn-primary elementor-button" onclick="addChild()">+ Weiteres Kind hinzufügen</button>

                        <!-- Weitere Leistungen für Kinder (einmalige Erfassung) -->
                        <div id="kinder_weitere_leistungen_wrapper" style="margin-top: 24px;">
                            <h4>Weitere regelmäßige Geldleistungen für Kinder</h4>
                            <p class="insolvenzo-info-text"><small>Diese Angaben werden nur einmal erfasst und gelten ergänzend zur Kind-Erfassung oben.</small></p>

                            <div id="kind_leistung_einmalig_wrapper">
                                <h6>Weitere regelmäßige Geldleistungen für Kinder</h6>
                                <div class="insolvenzo-subgroup">
                                    <div class="insolvenzo-form-group">
                                        <label>Bezeichnung der Leistung</label>
                                        <input type="text" name="kind_leistung[bezeichnung]" placeholder="z.B. Kinderzuschlag..." />
                                    </div>
                                    <div class="insolvenzo-form-group">
                                        <label>Monatlicher Betrag (€)</label>
                                        <input type="number" name="kind_leistung[betrag]" step="0.01" />
                                    </div>
                                    <div class="insolvenzo-form-group">
                                        <label>Nachweis liegt vor in Form von</label>
                                        <div class="insolvenzo-checkbox-group">
                                            <label><input type="checkbox" name="kind_leistung[nachweis]" value="bescheid" /> Bescheid</label>
                                            <label><input type="checkbox" name="kind_leistung[nachweis]" value="konto" /> Kontoauszug</label>
                                            <label><input type="checkbox" name="kind_leistung[nachweis]" value="sonst" /> Sonstiger geeigneter Nachweis</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="kind_selbst_einmalig_wrapper">
                                <h6>Weitere regelmäßige Geldleistung für das Kind selbst</h6>
                                <p class="insolvenzo-info-text"><small>Diese Zahlungen betreffen Geld für das Kind, nicht Ihre eigene Unterhaltspflicht.</small></p>

                                <div class="insolvenzo-subgroup">
                                    <div class="insolvenzo-form-group">
                                        <label>Art der Geldleistung für das Kind</label>
                                        <select id="kind_selbst_art" name="kind_selbst[art]">
                                            <option value="">Bitte wählen</option>
                                            <option value="kinderzuschlag">Kinderzuschlag</option>
                                            <option value="unterhaltsvorschuss">Unterhaltsvorschuss</option>
                                            <option value="barunterhalt">Laufender Barunterhalt für das Kind</option>
                                            <option value="sonstige">Sonstige regelmäßige Geldleistung für das Kind</option>
                                        </select>
                                    </div>

                                    <div id="kind_selbst_sonstige" class="insolvenzo-form-group" style="display: none;">
                                        <input type="text" name="kind_selbst[sonstige_art]" placeholder="Bitte angeben..." />
                                    </div>

                                    <div class="insolvenzo-form-group">
                                        <label>Monatlicher Betrag (€)</label>
                                        <input type="number" name="kind_selbst[betrag]" step="0.01" />
                                    </div>

                                    <div class="insolvenzo-form-group">
                                        <label>Nachweis liegt vor in Form von</label>
                                        <div class="insolvenzo-checkbox-group">
                                            <label><input type="checkbox" name="kind_selbst[nachweis]" value="bescheid" /> Bescheid (z.B. Kinderzuschlag, Unterhaltsvorschuss)</label>
                                            <label><input type="checkbox" name="kind_selbst[nachweis]" value="konto" /> Kontoauszug</label>
                                            <label><input type="checkbox" name="kind_selbst[nachweis]" value="sonst" /> Sonstiger geeigneter Nachweis</label>
                                        </div>
                                    </div>
                                </div>
                                <p class="insolvenzo-info-text"><small><strong>Hinweis:</strong> Tragen Sie hier nur Zahlungen ein, die Sie erhalten. Unterhaltspflichten, die Sie selbst erfüllen (Natural- oder Barunterhalt), werden nicht hier, sondern bereits beim Freibetrag berücksichtigt.</small></p>
                            </div>
                        </div>

                    </div>
                    <div class="insolvenzo-step-right">
                        <?php
                        $step4_text_title = isset($attributes['step4TextTitle']) ? $attributes['step4TextTitle'] : '';
                        $step4_text_content = isset($attributes['step4TextContent']) ? $attributes['step4TextContent'] : '';
                        $step4_video_url = isset($attributes['step4VideoUrl']) ? $attributes['step4VideoUrl'] : '';
                        $step4_has_text = !empty($step4_text_title) || !empty($step4_text_content);
                        $step4_has_video = !empty($step4_video_url);

                        insolvenzo_render_collapsible_text_box(
                            $step4_text_title,
                            $step4_text_content,
                            $step4_has_text
                        );
                        insolvenzo_render_collapsible_video_box($step4_video_url, ($step4_has_video && !$step4_has_text));
                        ?>
                    </div>
                </div>
            </div>

            <!-- Step 5: Einmalige Zahlungen (Freibeträge) -->
            <div class="insolvenzo-step" data-step-number="5">
                <h3><span class="insolvenzo-step-roman">V</span> Einmalige Zahlungen (Freibeträge)</h3>
                <div class="insolvenzo-step-two-columns">
                    <div class="insolvenzo-step-left">

                        <!-- 1. Einmalige Sozialleistungen -->
                        <h4>1. Einmalige Sozialleistungen</h4>
                        
                        <div class="insolvenzo-form-group">
                            <label for="sozialleistung_type">Art der Zahlung</label>
                            <select id="sozialleistung_type" name="sozialleistung_type">
                                <option value="">Bitte wählen</option>
                                <option value="erstausstattung">Einmalige Sozialleistung (z.B. Erstausstattung, Beihilfe)</option>
                                <option value="sonstige">Sonstige einmalige Sozialleistung</option>
                            </select>
                        </div>

                        <div id="sozialleistung_sonstige" class="insolvenzo-form-group" style="display: none;">
                            <input type="text" name="sozialleistung_sonstige_art" placeholder="Bitte beschreiben..." />
                        </div>

                        <div class="insolvenzo-form-group">
                            <label for="sozialleistung_amount">Einmaliger Betrag (€)</label>
                            <input type="number" id="sozialleistung_amount" name="sozialleistung_amount" step="0.01" />
                        </div>

                        <div class="insolvenzo-form-group">
                            <label>Nachweis liegt vor in Form von</label>
                            <div class="insolvenzo-checkbox-group">
                                <label><input type="checkbox" name="sozialleistung_nachweis" value="bescheid" /> Bescheid</label>
                                <label><input type="checkbox" name="sozialleistung_nachweis" value="sonst" /> Sonstiger geeigneter Nachweis</label>
                            </div>
                            <input type="text" name="sozialleistung_nachweis_sonst" placeholder="Bitte angeben..." class="insolvenzo-indent-input" style="display: none;" />
                        </div>

                        <!-- 2. Einmalige Geldleistungen nach landes- oder bundesrechtlichen Vorschriften -->
                        <h4 style="margin-top: 2rem;">2. Einmalige Geldleistungen nach landes- oder bundesrechtlichen Vorschriften</h4>
                        
                        <div class="insolvenzo-form-group">
                            <label for="bundesrecht_type">Art der Zahlung</label>
                            <select id="bundesrecht_type" name="bundesrecht_type">
                                <option value="">Bitte wählen</option>
                                <option value="entschaedigung">Einmalige Entschädigungs- oder Ausgleichsleistung</option>
                                <option value="unterstuetzung">Einmalige Unterstützung wegen besonderer Lebenslage</option>
                                <option value="sonstige">Sonstige einmalige gesetzliche Leistung</option>
                            </select>
                        </div>

                        <div id="bundesrecht_sonstige" class="insolvenzo-form-group" style="display: none;">
                            <input type="text" name="bundesrecht_sonstige_art" placeholder="Bitte beschreiben..." />
                        </div>

                        <div class="insolvenzo-form-group">
                            <label for="bundesrecht_amount">Einmaliger Betrag (€)</label>
                            <input type="number" id="bundesrecht_amount" name="bundesrecht_amount" step="0.01" />
                        </div>

                        <div class="insolvenzo-form-group">
                            <label>Nachweis liegt vor in Form von</label>
                            <div class="insolvenzo-checkbox-group">
                                <label><input type="checkbox" name="bundesrecht_nachweis" value="bescheid" /> Bescheid</label>
                                <label><input type="checkbox" name="bundesrecht_nachweis" value="sonst" /> Sonstiger geeigneter Nachweis</label>
                            </div>
                            <input type="text" name="bundesrecht_nachweis_sonst" placeholder="Bitte angeben..." class="insolvenzo-indent-input" style="display: none;" />
                        </div>

                        <!-- 3. Nachzahlung laufender Leistungen -->
                        <h4 style="margin-top: 2rem;">3. Nachzahlung laufender Leistungen (einmalige Auszahlung)</h4>
                        <p class="insolvenzo-info-text">Hier werden Nachzahlungen erfasst, die eigentlich laufende Leistungen betreffen, aber als Einmalbetrag ausgezahlt wurden.</p>
                        
                        <div class="insolvenzo-form-group">
                            <label for="nachzahlung_leistung_type">Welche Leistung wurde nachgezahlt?</label>
                            <select id="nachzahlung_leistung_type" name="nachzahlung_leistung_type">
                                <option value="">Bitte wählen</option>
                                <option value="buergergeld">Bürgergeld (SGB II)</option>
                                <option value="sozialhilfe">Sozialhilfe (SGB XII)</option>
                                <option value="asylblg">Leistungen nach AsylbLG</option>
                                <option value="kindergeld">Kindergeld</option>
                                <option value="weiterkinder">Weitere Geldleistungen für Kinder (z.B. Kinderzuschlag, Unterhaltsvorschuss)</option>
                                <option value="sonstige">Sonstige nachgezahlte Leistung</option>
                            </select>
                        </div>

                        <div id="nachzahlung_leistung_sonstige" class="insolvenzo-form-group" style="display: none;">
                            <input type="text" name="nachzahlung_leistung_sonstige_art" placeholder="Bitte beschreiben..." />
                        </div>

                        <div class="insolvenzo-form-group">
                            <label for="nachzahlung_leistung_amount">Einmaliger Nachzahlungsbetrag (€)</label>
                            <input type="number" id="nachzahlung_leistung_amount" name="nachzahlung_leistung_amount" step="0.01" />
                        </div>

                        <div class="insolvenzo-form-group">
                            <label>Nachweis liegt vor in Form von</label>
                            <div class="insolvenzo-checkbox-group">
                                <label><input type="checkbox" name="nachzahlung_leistung_nachweis" value="bescheid" /> Bescheid</label>
                                <label><input type="checkbox" name="nachzahlung_leistung_nachweis" value="sonst" /> Sonstiger geeigneter Nachweis</label>
                            </div>
                            <input type="text" name="nachzahlung_leistung_nachweis_sonst" placeholder="Bitte angeben..." class="insolvenzo-indent-input" style="display: none;" />
                        </div>

                        <!-- 4. Nachzahlung sonstiger laufender Geldleistungen oder Arbeitseinkommen bis 500 € -->
                        <h4 style="margin-top: 2rem;">4. Nachzahlung sonstiger laufender Geldleistungen oder Arbeitseinkommen bis 500 €</h4>
                        <p class="insolvenzo-info-text">Dieser Punkt gilt nur, wenn der Nachzahlungsbetrag maximal 500 € beträgt.</p>
                        
                        <div class="insolvenzo-form-group">
                            <label for="nachzahlung_500_type">Art der Nachzahlung</label>
                            <select id="nachzahlung_500_type" name="nachzahlung_500_type">
                                <option value="">Bitte wählen</option>
                                <option value="gehalt">Lohn-/Gehaltsnachzahlung</option>
                                <option value="leistung">Nachzahlung sonstiger laufender Leistung (nicht SGB II/XII/AsylbLG, nicht Kindergeld)</option>
                                <option value="sonstige">Sonstige Nachzahlung</option>
                            </select>
                        </div>

                        <div id="nachzahlung_500_sonstige" class="insolvenzo-form-group" style="display: none;">
                            <input type="text" name="nachzahlung_500_sonstige_art" placeholder="Bitte beschreiben..." />
                        </div>

                        <div class="insolvenzo-form-group">
                            <label for="nachzahlung_500_amount">Einmaliger Nachzahlungsbetrag (max. 500 €)</label>
                            <input type="number" id="nachzahlung_500_amount" name="nachzahlung_500_amount" step="0.01" max="500" />
                        </div>

                        <div class="insolvenzo-form-group">
                            <label>Nachweis liegt vor in Form von</label>
                            <div class="insolvenzo-checkbox-group">
                                <label><input type="checkbox" name="nachzahlung_500_nachweis" value="lohnabr" /> Lohnabrechnung</label>
                                <label><input type="checkbox" name="nachzahlung_500_nachweis" value="konto" /> Kontoauszug</label>
                                <label><input type="checkbox" name="nachzahlung_500_nachweis" value="sonst" /> Sonstiger geeigneter Nachweis</label>
                            </div>
                        </div>

                        <!-- 5. Stiftung "Mutter und Kind" -->
                        <h4 style="margin-top: 2rem;">5. Stiftung „Mutter und Kind – Schutz des ungeborenen Lebens"</h4>
                        
                        <div class="insolvenzo-form-group">
                            <label><input type="checkbox" name="mutter_kind_stiftung" value="ja" /> Einmalige Leistung der Stiftung „Mutter und Kind"</label>
                        </div>

                        <div id="mutter_kind_amount_wrapper" class="insolvenzo-form-group" style="display: none;">
                            <label for="mutter_kind_amount">Einmaliger Betrag (€)</label>
                            <input type="number" id="mutter_kind_amount" name="mutter_kind_amount" step="0.01" />
                        </div>

                        <div id="mutter_kind_nachweis_wrapper" class="insolvenzo-form-group" style="display: none;">
                            <label><input type="checkbox" name="mutter_kind_nachweis" value="bescheid" /> Bescheid / Bewilligung</label>
                        </div>

                    </div>
                    <div class="insolvenzo-step-right">
                        <?php
                        $step5_text_title = isset($attributes['step5TextTitle']) ? $attributes['step5TextTitle'] : '';
                        $step5_text_content = isset($attributes['step5TextContent']) ? $attributes['step5TextContent'] : '';
                        $step5_video_url = isset($attributes['step5VideoUrl']) ? $attributes['step5VideoUrl'] : '';
                        $step5_has_text = !empty($step5_text_title) || !empty($step5_text_content);
                        $step5_has_video = !empty($step5_video_url);

                        insolvenzo_render_collapsible_text_box(
                            $step5_text_title,
                            $step5_text_content,
                            $step5_has_text
                        );
                        insolvenzo_render_collapsible_video_box($step5_video_url, ($step5_has_video && !$step5_has_text));
                        ?>
                    </div>
                </div>
            </div>

            <!-- Navigation buttons -->
            <div class="insolvenzo-form-nav">
                <button type="button" class="insolvenzo-btn insolvenzo-btn-secondary elementor-button" data-step-prev disabled>
                    ← Zurück
                </button>
                <button type="button" class="insolvenzo-btn insolvenzo-btn-primary elementor-button" data-step-next>
                    Weiter →
                </button>
                <button type="submit" class="insolvenzo-btn insolvenzo-btn-success elementor-button" style="display:none;" id="submit-btn">
                    Abschicken
                </button>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Resolve Power Automate endpoint URL.
 *
 * Priority:
 * 1) INSOLVENZO_POWER_AUTOMATE_URL constant
 * 2) insolvenzo/power_automate_url filter
 */
function insolvenzo_get_power_automate_url() {
    $url = '';

    if (defined('INSOLVENZO_POWER_AUTOMATE_URL') && INSOLVENZO_POWER_AUTOMATE_URL) {
        $url = INSOLVENZO_POWER_AUTOMATE_URL;
    }

    $url = apply_filters('insolvenzo/power_automate_url', $url);
    $url = esc_url_raw((string) $url);

    return $url;
}

/**
 * Get best-effort client IP for rate-limiting.
 */
function insolvenzo_get_client_ip() {
    $candidates = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR',
    ];

    foreach ($candidates as $key) {
        if (empty($_SERVER[$key])) {
            continue;
        }

        $raw = (string) $_SERVER[$key];
        $parts = array_map('trim', explode(',', $raw));

        foreach ($parts as $part) {
            if (filter_var($part, FILTER_VALIDATE_IP)) {
                return $part;
            }
        }
    }

    return 'unknown';
}

/**
 * Simple transient-based rate limit.
 */
function insolvenzo_rate_limit_exceeded($client_ip) {
    $window_seconds = (int) apply_filters('insolvenzo/rate_limit_window_seconds', 600);
    $max_requests = (int) apply_filters('insolvenzo/rate_limit_max_requests', 10);

    if ($window_seconds < 60) {
        $window_seconds = 60;
    }

    if ($max_requests < 1) {
        $max_requests = 1;
    }

    $key = 'insolvenzo_rl_' . md5((string) $client_ip);
    $count = (int) get_transient($key);

    if ($count >= $max_requests) {
        return true;
    }

    set_transient($key, $count + 1, $window_seconds);
    return false;
}

/**
 * Basic recursive sanitizer for form payload.
 */
function insolvenzo_sanitize_payload($value) {
    if (is_array($value)) {
        $sanitized = [];
        foreach ($value as $key => $item) {
            $clean_key = is_string($key) ? sanitize_key($key) : $key;
            $sanitized[$clean_key] = insolvenzo_sanitize_payload($item);
        }
        return $sanitized;
    }

    if (is_bool($value) || is_int($value) || is_float($value) || is_null($value)) {
        return $value;
    }

    return sanitize_text_field((string) $value);
}

/**
 * REST callback: accepts frontend JSON and forwards it server-side to Power Automate.
 */
function insolvenzo_rest_submit_handler(WP_REST_Request $request) {
    $json = $request->get_json_params();
    if (!is_array($json) || empty($json)) {
        return new WP_Error(
            'invalid_payload',
            __('Es wurden keine Formulardaten übermittelt.', 'insolvenzo-form'),
            ['status' => 400]
        );
    }

    // Bot protection: honeypot must remain empty.
    if (!empty($json['website'])) {
        return new WP_Error(
            'invalid_request',
            __('Anfrage konnte nicht verarbeitet werden.', 'insolvenzo-form'),
            ['status' => 400]
        );
    }

    $client_ip = insolvenzo_get_client_ip();
    if (insolvenzo_rate_limit_exceeded($client_ip)) {
        return new WP_Error(
            'rate_limited',
            __('Zu viele Anfragen. Bitte versuchen Sie es in einigen Minuten erneut.', 'insolvenzo-form'),
            ['status' => 429]
        );
    }

    $nonce = $request->get_header('x_wp_nonce');
    if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error(
            'invalid_nonce',
            __('Ungültiger Sicherheits-Token. Bitte Seite neu laden und erneut versuchen.', 'insolvenzo-form'),
            ['status' => 403]
        );
    }

    $email = isset($json['contact_email']) ? sanitize_email((string) $json['contact_email']) : '';
    if (empty($email) || !is_email($email)) {
        return new WP_Error(
            'invalid_email',
            __('Bitte eine gültige E-Mail-Adresse angeben.', 'insolvenzo-form'),
            ['status' => 422]
        );
    }

    $forward_url = insolvenzo_get_power_automate_url();
    if (empty($forward_url)) {
        return new WP_Error(
            'missing_power_automate_url',
            __('Power-Automate-URL ist nicht konfiguriert.', 'insolvenzo-form'),
            ['status' => 500]
        );
    }

    $scheme = strtolower((string) wp_parse_url($forward_url, PHP_URL_SCHEME));
    if ($scheme !== 'https') {
        return new WP_Error(
            'insecure_power_automate_url',
            __('Power-Automate-URL muss HTTPS verwenden.', 'insolvenzo-form'),
            ['status' => 500]
        );
    }

    $allowed_hosts = apply_filters('insolvenzo/power_automate_allowed_hosts', []);
    if (is_array($allowed_hosts) && !empty($allowed_hosts)) {
        $host = strtolower((string) wp_parse_url($forward_url, PHP_URL_HOST));
        $allowed_hosts = array_map(static function ($item) {
            return strtolower((string) $item);
        }, $allowed_hosts);

        if (!$host || !in_array($host, $allowed_hosts, true)) {
            return new WP_Error(
                'disallowed_power_automate_host',
                __('Power-Automate-Host ist nicht erlaubt.', 'insolvenzo-form'),
                ['status' => 500]
            );
        }
    }

    $payload = [
        'formData' => insolvenzo_sanitize_payload($json),
        'meta' => [
            'submitted_at_utc' => gmdate('c'),
            'site_url' => site_url(),
            'wp_nonce_valid' => true,
        ],
    ];

    $response = wp_remote_post(
        $forward_url,
        [
            'method' => 'POST',
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
            ],
            'body' => wp_json_encode($payload),
        ]
    );

    if (is_wp_error($response)) {
        return new WP_Error(
            'power_automate_request_failed',
            __('Weiterleitung an Power Automate fehlgeschlagen.', 'insolvenzo-form'),
            ['status' => 502]
        );
    }

    $status_code = (int) wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    if ($status_code < 200 || $status_code >= 300) {
        return new WP_Error(
            'power_automate_bad_response',
            __('Power Automate hat die Anfrage nicht akzeptiert.', 'insolvenzo-form'),
            ['status' => 502]
        );
    }

    return new WP_REST_Response(
        [
            'success' => true,
            'message' => __('Formular erfolgreich übermittelt.', 'insolvenzo-form'),
            'forward_status' => $status_code,
        ],
        200
    );
}

add_action('rest_api_init', function () {
    register_rest_route('insolvenzo/v1', '/submit', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'insolvenzo_rest_submit_handler',
        'permission_callback' => '__return_true',
    ]);
});

add_action('init', function () {
    register_block_type(__DIR__ . '/build', [
        'render_callback' => 'insolvenzo_form_render_callback',
    ]);
});