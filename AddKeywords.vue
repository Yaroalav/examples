<template>
    <div
        v-if="!isViewOnlyUser"
        class="container-fluid screen"
    >
        <div class="row">
            <a
                class="screen__back-link"
                data-cy="add-keyword-cancel"
                nohref
                @click="closeAddKeywords()"
            >
                <i class="icon-chevron-left" />
                {{ groupName }} {{ $t('group') }} <span v-if="count">({{ count }} {{ $t('keywords') }})</span>
            </a>
        </div>
        <div class="row">
            <h2 class="screen__title screen__title_add-keyword">{{ $t('add-keywords') }}</h2>
        </div>
        <div class="row">
            <tabs>
                <tab header="Web domain">
                    <web-domain-tab />
                </tab>
                <tab header="Google local maps">
                    <google-local-maps-tab />
                </tab>
                <tab :header="$t('youtube-videos')">
                    <youtube-videos-tab />
                </tab>
            </tabs>
        </div>
    </div>
</template>

<script>

    import { mapGetters } from 'vuex';
    import { tabs, tab } from 'vue-strap';

    const WebDomainTab = () => import('@/components/add-keywords/WebDomainTab');
    const GoogleLocalMapsTab = () => import('@/components/add-keywords/GoogleLocalMapsTab');
    const YoutubeVideosTab = () => import('@/components/add-keywords/YoutubeVideosTab');

    export default {
        name: 'AddKeywordsScreen',
        components: {
            'tabs': tabs,
            'tab': tab,
            'web-domain-tab': WebDomainTab,
            'google-local-maps-tab': GoogleLocalMapsTab,
            'youtube-videos-tab': YoutubeVideosTab,
        },
        computed: {
            ...mapGetters([
                'getCurrentGroup',
                'isViewOnlyUser',
            ]),
            count () {
                return this.getCurrentGroup ? this.getCurrentGroup.keywords_count.ACTIVE : '';
            },
            groupName () {
                return this.getCurrentGroup ? this.getCurrentGroup.name : '';
            },
        },
        watch:{
            async '$route' (cur, prev) {
                if (prev.name === 'addKeywords') {
                    this.$destroy();
                }
            }
        },
        methods: {
            closeAddKeywords () {
                this.getCurrentGroup
                    ? this.$router.push({ name: 'keywordList', params: this.$route.params })
                    : this.$router.go(-1);
            },
        },
    }

</script>

<style lang="scss" scoped>

    @import '../../../sass/variables';
    @import '../../../sass/nav-tabs';

    .screen__title_add-keyword {
        margin-bottom: 44px;
    }

    /deep/ .nav-tabs {
        @extend .custom-nav-tabs;
    }

</style>
