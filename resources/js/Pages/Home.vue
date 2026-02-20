<template>
    <v-container class="page-container mt-8">
        <v-card elevation="4" rounded="xl" class="mb-4">
            <v-card-title class="text-h6 font-weight-bold">Mandatory Fields</v-card-title>

            <v-card-text>
                <v-text-field
                    label="Application name"
                    variant="filled"
                    v-model='appName'
                    density="compact"
                />

                <v-select
                    chips
                    label="Services"
                    :items="services"
                    v-model='selectedServices'
                    variant="filled"
                    density="compact"
                    multiple
                />
            </v-card-text>
        </v-card>

        <v-card elevation="4" rounded="xl" class="mb-4">
            <v-card-title class="text-h6 font-weight-bold">Optional Fields</v-card-title>

            <v-card-text>
                <v-row>
                    <v-col cols='12' sm='6' md='4'>
                        <v-select
                            chips
                            label="Starter Kit"
                            :items="starterKit"
                            v-model='selectedStarterKit'
                            variant="filled"
                            density="compact"
                        />
                    </v-col>

                    <v-col v-if="selectedStarterKit === 'custom'" cols='12' sm='6' md='4'>
                        <v-text-field
                            label="Custom Starter Kit URL"
                            :error-messages="customStarterKitUrlError"
                            v-model='customStarterKitUrl'
                            variant="filled"
                            density="compact"
                            @blur="validateCustomUrl"
                        />
                    </v-col>

                    <v-col cols='12' sm='6' md='4'>
                        <v-select
                            chips
                            label="JavaScript Runtime"
                            :items="javascriptRuntime"
                            v-model='selectedJavascriptRuntime'
                            variant="filled"
                            density="compact"
                        />
                    </v-col>

                    <v-col cols='12' sm='6' md='4'>
                        <v-select
                            chips
                            label="TestingFramework"
                            :items="testingFramework"
                            v-model='selectedTesting'
                            variant="filled"
                            density="compact"
                        />
                    </v-col>

                    <v-col v-if="selectedStarterKit !== 'custom'" cols='12' sm='6' md='4' >
                        <v-select
                            chips
                            label="Auth Provider"
                            :items="authProvider"
                            v-model='selectedAuth'
                            variant="filled"
                            density="compact"
                        />
                    </v-col>

                    <v-col cols='12' sm='6' md='4' class="d-flex align-center">
                        <v-switch
                            label="Install Laravel Boost"
                            v-model='withBoost'
                            density="compact"
                            hide-details
                        />
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <code-block :code='command' />
    </v-container>
</template>

<script setup>
    import { ref,computed } from 'vue'
    import { usePage } from '@inertiajs/vue3'
    import CodeBlock from '../components/CodeBlock.vue'

    const page = usePage()
    const url = computed(() => page.props.url ) //Shared from HomeController

    const appName = ref('Laravel')
    const selectedServices = ref([
        'pgsql',
        'redis',
        'meilisearch',
        'minio',
        'mailpit',
        'selenium'
    ])
    const selectedStarterKit = ref('livewire')
    const customStarterKitUrl = ref('')
    const customStarterKitUrlError = ref('')
    const selectedJavascriptRuntime = ref('npm')
    const selectedAuth = ref('laravel')
    const selectedTesting = ref('pest')
    const withBoost = ref(false)

    //Select Boxes items
    const services = ref([
        'mysql',
        'pgsql',
        'mariadb',
        'mongodb',
        'redis',
        'valkey',
        'memcached',
        'meilisearch',
        'typesense',
        'minio',
        'rustfs',
        'mailpit',
        'rabbitmq',
        'selenium',
        'soketi',
    ])
    const starterKit = ref([
        'none',
        'livewire',
        'livewire-class-components',
        'vue',
        'react',
        'svelte',
        'custom',
    ])
    const javascriptRuntime = ref([
        'npm',
        'pnpm',
        'bun',
        'yarn',
    ])
    const authProvider = ref([
        'no-authentication',
        'laravel',
        'workos',
    ])
    const testingFramework = ref([
        'pest',
        'phpunit',
    ])


    const validateCustomUrl = () => {
        if (selectedStarterKit.value === 'custom') {
            if (!customStarterKitUrl.value) {
                customStarterKitUrlError.value = 'URL is required for custom starter kit'
                return false
            }

            try {
                new URL(customStarterKitUrl.value)
                customStarterKitUrlError.value = ''
                return true
            } catch (error) {
                customStarterKitUrlError.value = 'Please enter a valid URL'
                return false
            }
        }
        customStarterKitUrlError.value = ''
        return true
    }

    const command = computed(() => {
        const url = window.location.href+appName.value
        const services = '?with='+selectedServices.value.join(',')
        const frontend = '&frontend='+selectedStarterKit.value
        const javascriptRuntime = '&javascript='+selectedJavascriptRuntime.value
        const testing = '&testing='+selectedTesting.value
        let auth = ''
        let using = ''

        if(selectedStarterKit.value !== 'custom' && selectedAuth.value !== 'laravel') {
            auth = '&auth='+selectedAuth.value
        }

        if(selectedStarterKit.value === 'custom' && customStarterKitUrl.value) {
            using = '&using='+encodeURIComponent(customStarterKitUrl.value)
        }

        const boost = withBoost.value ? '&boost' : ''

        const command = "curl -s '"+url+services+frontend+javascriptRuntime+testing+auth+boost+using+"' | bash"

        return command
    })
</script>

<style scoped>
    .page-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 16px;
    }
</style>
