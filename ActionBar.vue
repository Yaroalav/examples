<template>
    <div :class="actionBarClasses">
        <div class="action-bar__button-group action-bar__button-group_left">
            <div :class="actionBarDescriptionClasses">
                <i v-html="getSelectedAuth.length" />
                {{ $t('selected') }}
            </div>

            <template v-if="!getViewkeyView && !isViewOnlyUser">
                <button
                    type="button"
                    class="button-action-bar"
                    @click="addToFavorite"
                >
                    <i class="icon-new-outline-star" />
                    Add Star
                </button>

                <button
                    type="button"
                    class="button-action-bar"
                    @click="removeFromFavorite"
                >
                    <i class="icon-new-cross-star" />
                    Remove Star
                </button>
            </template>
        </div>
        <div class="action-bar__button-group action-bar__button-group_central">
            <button
                v-if="!getViewkeyView && !isViewOnlyUser"
                id="multi-edit-button"
                class="button-action-bar"
                type="button"
                @click.stop="emitEdit"
            >
                <i class="icon-new-multiple-edit" />
                {{ $t('edit') }}
            </button>

            <button
                v-if="!getViewkeyView && !isViewOnlyUser"
                class="button-action-bar"
                type="button"
                @click.stop="openTagsManageModal"
            >
                <i class="icon-new-manage-tag-multiple" />
                {{ $t('manage-tags') }}
            </button>

            <button
                class="button-action-bar"
                type="button"
                @click="toggleCommonKeywordsCharts"
            >
                <i class="icon-new-multiple-charts" />
                <span>
                    {{ getCommonKeywordsChartIsShow ? 'Hide' : 'Show' }} {{ $t('charts') }}
                </span>
            </button>

            <button
                v-if="!isViewOnlyUser"
                class="button-action-bar"
                type="button"
                @click="exportSelectedAsTXT"
            >
                <i class="icon-manage-export" />
                {{ $t('export-keywords') }}
            </button>
        </div>
        <div class="action-bar__button-group action-bar__button-group_right">
            <template v-if="!getViewkeyView && !isViewOnlyUser">
                <button
                    class="button-action-bar"
                    type="button"
                    @click="openDeleteKeywordsModal"
                >
                    <i class="icon-new-multiple-trash" />
                    {{ $t('delete') }}
                </button>

                <button
                    class="button-action-bar button-action-bar_last-button"
                    type="button"
                    @click="toggleAllKeywords"
                >
                    Deselect All
                </button>
            </template>
        </div>
    </div>
</template>

<script>

    import { mapGetters, mapActions, mapMutations } from 'vuex';
    import { Events, EventBus } from '@/events';
    import { exportSelectedKeywordsAsTXT } from '@/helpers/downloads-service';
    import ConfirmDeletingKeywords from '@/components/modals/ConfirmDeletingKeywords';
    import DashboardMixin from '@/mixins/DashboardMixin';
    import TagsManageModal from '@/components/modals/TagsManageModal';

    export default {
        name: 'ActionBar',
        mixins: [
            DashboardMixin,
        ],
        computed: {
            ...mapGetters([
                'getSelectedKeywords',
                'getViewkeyView',
                'getCommonKeywordsChartIsShow',
                'isViewOnlyUser',
                'getSelectedAuth',
            ]),
            actionBarClasses () {
                return {
                    'action-bar': true,
                    'action-bar__wrapper_small-resolution': window.screen.width < 1380,
                };
            },
            actionBarDescriptionClasses () {
                return {
                    'action-bar__description': true,
                    'action-bar__description_small': this.getSelectedAuth.length > 999,
                };
            },
        },
        methods: {
            ...mapMutations([
                'setShownChart',
                'toggleCommonKeywordsChartsIsShow',
            ]),
            ...mapActions([
                'toggleSidebar',
                'addKeywordsToFavorite',
                'removeKeywordsFromFavorite',
            ]),
            emitEdit () {
                EventBus.emit(Events.SET_EDITABLE_KEYWORDS, {
                    keywordAuth: this.getSelectedKeywords[0].auth,
                    multiple: this.getSelectedKeywords.length > 1
                });
            },
            openDeleteKeywordsModal () {
                this.$modal.show(
                    ConfirmDeletingKeywords,
                    { keywords: this.getSelectedKeywords },
                    {
                        width: 282,
                        height: 'auto',
                        pivotX: 0,
                        classes: 'v--modal center-modal-popup',
                        clickToClose: window.screen.width > window.MOBILE_WIDTH_RESOLUTION_PHONE,
                    },
                );
            },
            openTagsManageModal (e) {
                this.$modal.show(
                    TagsManageModal,
                    { keywords: this.getSelectedKeywords },
                    {
                        height: 'auto',
                        pivotX: 0,
                        name: 'TagsManageModal tags-mobal-window',
                        width: 266,
                        classes: 'v--modal',
                    },
                    {
                        'opened': () => {
                            const modal = document.getElementsByClassName('v--modal')[0];
                            modal.style.marginLeft = (e.screenX - e.offsetX) + 'px';
                        }
                    }
                );
            },
            async addToFavorite () {
                try {
                    await this.addKeywordsToFavorite(this.getSelectedKeywords);
                    this.removeAllChecks();
                    this.setCheckboxesOnUpdate();
                    this.$toastr.s(this.$t('success-keywords-were-added-to-favorites'));
                } catch (error) {
                    this.$toastr.e(error);
                }
            },
            async removeFromFavorite () {
                try {
                    await this.removeKeywordsFromFavorite(this.getSelectedKeywords);
                    this.removeAllChecks();
                    this.setCheckboxesOnUpdate();
                    this.$toastr.s(this.$t('success-keywords-were-removed-from-favorites'));
                } catch (error) {
                    this.$toastr.e(error);
                }
            },
            toggleCommonKeywordsCharts () {
                this.setShownChart(null);
                this.toggleCommonKeywordsChartsIsShow();
            },
            exportSelectedAsTXT () {
                exportSelectedKeywordsAsTXT(this.getSelectedKeywords);
            },
        }
    }

</script>

<style lang="scss">

    @import '../../../sass/variables';

    .action-bar {
        align-items: center;
        background-color: $primary-white;
        bottom: 0;
        box-shadow: 0px -8px 20px rgba($shadows-color, 0.1);
        color: $text-grey;
        display: flex;
        flex-flow: row nowrap;
        height: 70px;
        justify-content: space-between;
        position: fixed;
        width: 100%;
        z-index: 1059;

        &.action-bar__wrapper_small-resolution {
            padding: 0 0px 20px 0px;
            position: sticky;
        }
    }

    .action-bar__button-group {
        align-items: center;
        display: flex;
        flex-flow: row nowrap;
        position: relative;
    }

    .action-bar__button-group_left {
        flex: 1 0 auto;
    }

    .action-bar__button-group_central {
        flex: 3 0 auto;
        justify-content: center;
    }

    .action-bar__button-group_right {
        flex: 2 0 auto;
        justify-content: flex-end;
    }

    .action-bar__description {
        color: #000;
        cursor: default;
        font-size: 14px;
        padding: 0 24px;

        i {
            background: $primary-blue;
            border-radius: 50%;
            color: $primary-white;
            display: inline-block;
            font-style: normal;
            height: 26px;
            margin-right: 8px;
            padding-top: 3px;
            text-align: center;
            user-select: none;
            width: 26px;
        }
    }

    .action-bar__description_small {
        font-size: 12px;

        i {
            height: 32px;
            padding-top: 8px;
            width: 32px;
        }
    }

    .action-bar__dropdown {
        position: relative;
    }

    .button-action-bar {
        @include r-med;
        background: transparent;
        border: none;
        color: $text-grey;
        font-size: 13px;
        line-height: 15px;
        padding: 0 16px;

        i {
            align-items: flex-end;
            color: $icons-and-secondary-text-gray;
            display: flex;
            font-size: 18px;
            height: 18px;
            margin-right: 8px;
            width: 18px;
        }

        &:hover {
            color: $text-black;

            i {
                color: $primary-blue;
            }
        }
    }

    .button-action-bar_last-button {
        margin-right: 100px;
    }

</style>
