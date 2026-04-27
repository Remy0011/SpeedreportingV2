import { engine, createTimeline, utils } from 'animejs';

engine.useDefaultMainLoop = false;

const [$container] = utils.$('.container');
const color = utils.get($container, 'color');
const { width, height } = $container.getBoundingClientRect();

const renderer = new THREE.WebGLRenderer({ alpha: true });
const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(65, width / height, 0.1, 20);
const geometry = new THREE.BoxGeometry(1, 1, 1);
const material = new THREE.MeshBasicMaterial({ color, wireframe: true });

renderer.setSize(width, height);
renderer.setPixelRatio(window.devicePixelRatio);
$container.appendChild(renderer.domElement);
camera.position.z = 5;

function createAnimatedCube() {
    const cube = new THREE.Mesh(geometry, material);
    const x = utils.random(-10, 10, 2);
    const y = utils.random(-5, 5, 2);
    const z = [-10, 7];
    const r = () => utils.random(-Math.PI * 2, Math.PI * 2, 3);
    const duration = 4000;
    createTimeline({
        delay: utils.random(0, duration),
        defaults: { loop: true, duration, ease: 'inSine', },
    })
        .add(cube.position, { x, y, z }, 0)
        .add(cube.rotation, { x: r, y: r, z: r }, 0)
        .init();
    scene.add(cube);
}

for (let i = 0; i < 40; i++) {
    createAnimatedCube();
}

function render() {
    engine.update();
    renderer.render(scene, camera);
}

renderer.setAnimationLoop(render);