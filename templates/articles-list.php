<?php
/**
 * Rektic WP
 *
 * @package   rektic-wp
 * @author    SAKHRAOUI Omar <info@omarion.me>
 * @copyright 2022 Rektic WP
 * @license   GPLv3
 * @link      https://omarion.me
 */
?>
<div class="wrap">
    <h2><?php
		echo __('Rektic Articles list', 'rektic-wp') ?></h2>
    <p><?php
		echo __('Articles list present in your account', 'rektic-wp') ?></p>
    <input type="hidden" id="nonce2" value="<?php
	echo esc_attr($nonce2) ?>"/>
    <table class="wp-list-table widefat fixed striped table-view-list posts">
        <thead>
        <tr>
            <th scope="col" id="title" class="manage-column column-title column-primary sortable asc">
                <a>
                    <span>Title</span>

                </a>
            </th>
            <th scope="col" id="date" class="manage-column column-date sortable asc">
                <a>
                    <span><?php
						echo __('Language', 'rektic-wp') ?></span>
                </a>
            </th>
            <th scope="col" id="date" class="manage-column column-date sortable asc">
                <a>
                    <span><?php
						echo __('Country', 'rektic-wp') ?></span>
                </a>
            </th>
            <th scope="col" id="date" class="manage-column column-date sortable asc">
                <a>
                    <span><?php
						echo __('Last Modified', 'rektic-wp') ?></span>
                </a>
            </th>
            <th scope="col" id="export" class="manage-column column-date sortable asc">
                <a>
                    <span><?php
						echo __('Export', 'rektic-wp') ?></span>
                </a>
            </th>
        </tr>
        </thead>

        <tbody id="the-list">
		<?php
		foreach($this->rekticArticles as $rektickArticle): ?>
            <tr id="post-3" class="iedit author-self level-0 post-3 type-page status-draft hentry">
                <td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
                    <div class="locked-info">
                        <span class="locked-avatar"></span>
                        <span class="locked-text"></span>
                    </div>
                    <strong><span class="row-title"
                                  aria-label="“<?php
								  echo esc_attr($rektickArticle->h1) ?? '' ?>”">
								<?php
								echo esc_attr($rektickArticle->h1) ?? '--' ?></span>
                    </strong>

                </td>
                <td class="language column-language" data-colname="Language"><?php
					echo esc_attr($rektickArticle->lang) ?></td>
                <td class="country column-country" data-colname="Country">
					<?php
					echo esc_attr($rektickArticle->country) ?>
                </td>
                <td class="date column-date" data-colname="Date">
					<?php
					echo wp_date('j F Y \<\b\r\> H:i:s', strtotime($rektickArticle->updatedAt)) ?>
                </td>
                <td class="export column-export" data-colname="export">
					<?php
					if($rektickArticle->step == 'DRAFT'): ?>
                        <a class="rektic-export" data-id="<?php
						echo esc_attr($rektickArticle->id) ?>">
                            <span><?php
								echo __('Create article', 'rektic-wp') ?></span>
                        </a>
					<?php
					else:
						?>
                        --
					<?php
					endif;
					?>
                </td>
            </tr>
		<?php
		endforeach; ?>
        </tbody>

        <tfoot>
        <tr>

            <th scope="col" id="title" class="manage-column column-title column-primary sortable asc">
                <a>
                    <span>Title</span>

                </a>
            </th>
            <th scope="col" id="date" class="manage-column column-date sortable asc">
                <a>
                    <span>Language</span>

                </a>
            </th>
            <th scope="col" id="date" class="manage-column column-date sortable asc">
                <a>
                    <span>Country</span>

                </a>
            </th>
            <th scope="col" id="date" class="manage-column column-date sortable asc">
                <a>
                    <span>Last Modified</span>

                </a>
            </th>
            <th scope="col" id="export" class="manage-column column-date sortable asc">
                <a>
                    <span>Export</span>

                </a>
            </th>
        </tr>
        </tfoot>

    </table>

</div>
