<script setup>
import AboutSection from '@/Components/Landing/AboutSection.vue';
import AppPreviewSection from '@/Components/Landing/AppPreviewSection.vue';
import CallToAction from '@/Components/Landing/CallToAction.vue';
import DownloadSection from '@/Components/Landing/DownloadSection.vue';
import FeaturesSection from '@/Components/Landing/FeaturesSection.vue';
import Footer from '@/Components/Landing/Footer.vue';
import HeroSection from '@/Components/Landing/HeroSection.vue';
import Navbar from '@/Components/Landing/Navbar.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
});

const description =
    'O Hazak conecta tecnologia, pessoas e recursos de segurança em uma única plataforma, ajudando você a permanecer conectado e protegido onde estiver.';
</script>

<template>
    <Head title="Hazak | Tecnologia para uma comunidade mais segura">
        <meta name="description" :content="description" />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="Hazak | Tecnologia para uma comunidade mais segura" />
        <meta property="og:description" :content="description" />
        <meta property="og:image" content="/images/landing/hero.jpg" />
        <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <div class="bg-hazak-black">
        <Navbar :can-login="canLogin" :can-register="canRegister" />

        <main>
            <HeroSection />
            <AboutSection />
            <FeaturesSection />
            <AppPreviewSection />
            <DownloadSection />
            <CallToAction :can-login="canLogin" :can-register="canRegister" />
        </main>

        <Footer :can-login="canLogin" :can-register="canRegister" />
    </div>
</template>
