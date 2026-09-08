<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$info = (array) $this->info;
$diagnostics = (array) ($info['diagnostics'] ?? []);
$systemOk = !empty($info['systemOk']);
$coreCompatible = !empty($info['coreCompatible']);
$connected = (array) ($info['connectedComponents'] ?? []);
?>
<div class="xdecaro-scope decaroprotocol-info" data-xdecaro-core-ui="<?php echo $this->coreUiActive ? '1' : '0'; ?>">
    <header class="decaroprotocol-info__header">
        <div>
            <span class="decaroprotocol-info__eyebrow">Protocol by xdecaro</span>
            <h1><?php echo Text::_('COM_DECAROPROTOCOL_INFORMATION'); ?></h1>
            <p><?php echo Text::_('COM_DECAROPROTOCOL_INFO_DESCRIPTION'); ?></p>
        </div>
        <span class="decaroprotocol-info__badge <?php echo $systemOk ? 'is-ok' : 'is-warning'; ?>">
            <?php echo Text::_($systemOk ? 'COM_DECAROPROTOCOL_INFO_SYSTEM_OK' : 'COM_DECAROPROTOCOL_INFO_SYSTEM_CHECK'); ?>
        </span>
    </header>

    <div class="decaroprotocol-info__grid">
        <section class="decaroprotocol-info__card">
            <h2><?php echo Text::_('COM_DECAROPROTOCOL_INFO_PRODUCT'); ?></h2>
            <dl>
                <div><dt><?php echo Text::_('COM_DECAROPROTOCOL_INFO_COMPONENT_VERSION'); ?></dt><dd><?php echo $escape($info['componentVersion'] ?? '—'); ?></dd></div>
                <div><dt><?php echo Text::_('COM_DECAROPROTOCOL_INFO_PACKAGE_VERSION'); ?></dt><dd><?php echo $escape(($info['packageVersion'] ?? '') ?: '—'); ?></dd></div>
                <div><dt><?php echo Text::_('COM_DECAROPROTOCOL_INFO_SCHEMA_VERSION'); ?></dt><dd><?php echo $escape(($info['schemaVersion'] ?? '') ?: '—'); ?></dd></div>
                <div><dt><?php echo Text::_('COM_DECAROPROTOCOL_INFO_COMPONENT_ID'); ?></dt><dd><code>com_decaroprotocol</code></dd></div>
                <div><dt><?php echo Text::_('COM_DECAROPROTOCOL_INFO_PACKAGE_ID'); ?></dt><dd><code>pkg_decaroprotocol</code></dd></div>
            </dl>
        </section>

        <section class="decaroprotocol-info__card">
            <h2><?php echo Text::_('COM_DECAROPROTOCOL_INFO_ENVIRONMENT'); ?></h2>
            <dl>
                <div><dt>Joomla</dt><dd><?php echo $escape($info['joomlaVersion'] ?? '—'); ?> <small>≥ <?php echo $escape($info['minimumJoomla'] ?? '—'); ?></small></dd></div>
                <div><dt>PHP</dt><dd><?php echo $escape($info['phpVersion'] ?? '—'); ?> <small>≥ <?php echo $escape($info['minimumPhp'] ?? '—'); ?></small></dd></div>
                <div><dt><?php echo Text::_('COM_DECAROPROTOCOL_INFO_DATABASE'); ?></dt><dd><?php echo $escape(trim((string) ($info['databaseType'] ?? '') . ' ' . (string) ($info['databaseVersion'] ?? '')) ?: '—'); ?></dd></div>
                <div><dt><?php echo Text::_('COM_DECAROPROTOCOL_INFO_TABLES'); ?></dt><dd><?php echo (int) ($info['tablePresentCount'] ?? 0); ?>/<?php echo (int) ($info['tableExpectedCount'] ?? 0); ?></dd></div>
            </dl>
        </section>

        <section class="decaroprotocol-info__card">
            <h2><?php echo Text::_('COM_DECAROPROTOCOL_INFO_EXTENSIONS'); ?></h2>
            <dl>
                <div><dt>Component</dt><dd><span class="decaroprotocol-info__badge is-ok">com_decaroprotocol</span></dd></div>
                <div><dt>Package</dt><dd><span class="decaroprotocol-info__badge <?php echo !empty($diagnostics['packageDetected']) ? 'is-ok' : 'is-warning'; ?>">pkg_decaroprotocol</span></dd></div>
                <div><dt>Core by xdecaro</dt><dd><span class="decaroprotocol-info__badge <?php echo $coreCompatible ? 'is-ok' : 'is-neutral'; ?>"><?php echo $escape(($info['coreVersion'] ?? '') ?: Text::_('COM_DECAROPROTOCOL_INFO_NOT_DETECTED')); ?></span></dd></div>
                <div><dt><?php echo Text::_('COM_DECAROPROTOCOL_INFO_CORE_UI'); ?></dt><dd><?php echo Text::_($this->coreUiActive ? 'COM_DECAROPROTOCOL_INFO_ACTIVE' : 'COM_DECAROPROTOCOL_INFO_FALLBACK'); ?></dd></div>
            </dl>
        </section>

        <section class="decaroprotocol-info__card">
            <h2><?php echo Text::_('COM_DECAROPROTOCOL_INFO_UPDATES'); ?></h2>
            <dl>
                <div><dt><?php echo Text::_('COM_DECAROPROTOCOL_INFO_CURRENT_VERSION'); ?></dt><dd><?php echo $escape($info['componentVersion'] ?? '—'); ?></dd></div>
                <div><dt><?php echo Text::_('COM_DECAROPROTOCOL_INFO_CHANNEL'); ?></dt><dd><?php echo Text::_('COM_DECAROPROTOCOL_INFO_GITHUB_BUILD'); ?></dd></div>
                <div><dt><?php echo Text::_('COM_DECAROPROTOCOL_INFO_AUTO_UPDATE'); ?></dt><dd><?php echo Text::_('COM_DECAROPROTOCOL_INFO_NOT_CONFIGURED'); ?></dd></div>
            </dl>
        </section>
    </div>

    <section class="decaroprotocol-info__card decaroprotocol-info__full">
        <h2><?php echo Text::_('COM_DECAROPROTOCOL_INFO_CONNECTED_COMPONENTS'); ?></h2>
        <p><?php echo Text::_('COM_DECAROPROTOCOL_INFO_CONNECTED_COMPONENTS_DESC'); ?></p>
        <div class="decaroprotocol-info__integrations">
            <?php foreach ($connected as $item) : ?>
                <article>
                    <div>
                        <strong><?php echo $escape($item['label'] ?? ''); ?></strong>
                        <code><?php echo $escape($item['element'] ?? ''); ?></code>
                    </div>
                    <span class="decaroprotocol-info__badge <?php echo !empty($item['installed']) ? 'is-ok' : 'is-neutral'; ?>">
                        <?php echo !empty($item['installed'])
                            ? $escape(($item['version'] ?? '') ?: Text::_('COM_DECAROPROTOCOL_INFO_INSTALLED'))
                            : Text::_('COM_DECAROPROTOCOL_INFO_NOT_INSTALLED'); ?>
                    </span>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="decaroprotocol-info__card decaroprotocol-info__full">
        <h2><?php echo Text::_('COM_DECAROPROTOCOL_INFO_DIAGNOSTICS'); ?></h2>
        <div class="decaroprotocol-info__diagnostics">
            <?php foreach ([
                'tablesPresent' => 'COM_DECAROPROTOCOL_INFO_CHECK_TABLES',
                'schemaAligned' => 'COM_DECAROPROTOCOL_INFO_CHECK_SCHEMA',
                'packageDetected' => 'COM_DECAROPROTOCOL_INFO_CHECK_PACKAGE',
                'installationConsistent' => 'COM_DECAROPROTOCOL_INFO_CHECK_INSTALLATION',
                'environmentCompatible' => 'COM_DECAROPROTOCOL_INFO_CHECK_ENVIRONMENT',
            ] as $key => $label) : ?>
                <div>
                    <span><?php echo Text::_($label); ?></span>
                    <span class="decaroprotocol-info__badge <?php echo !empty($diagnostics[$key]) ? 'is-ok' : 'is-warning'; ?>">
                        <?php echo Text::_(!empty($diagnostics[$key]) ? 'COM_DECAROPROTOCOL_INFO_OK' : 'COM_DECAROPROTOCOL_INFO_CHECK'); ?>
                    </span>
                </div>
            <?php endforeach; ?>
            <div>
                <span><?php echo Text::_('COM_DECAROPROTOCOL_INFO_CHECK_CORE_CONTRACTS'); ?></span>
                <span class="decaroprotocol-info__badge <?php echo !empty($info['coreContracts']) ? 'is-ok' : 'is-neutral'; ?>">
                    <?php echo Text::_(!empty($info['coreContracts']) ? 'COM_DECAROPROTOCOL_INFO_AVAILABLE' : 'COM_DECAROPROTOCOL_INFO_OPTIONAL'); ?>
                </span>
            </div>
        </div>
    </section>
</div>
