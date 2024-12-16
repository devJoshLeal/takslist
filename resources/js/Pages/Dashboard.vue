<script setup>
    import { usePage } from '@inertiajs/vue3'; // Importa usePage
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head } from '@inertiajs/vue3';
    import { onMounted, ref, nextTick } from 'vue';
    import axios from 'axios'; // Importar Axios

    const { props } = usePage(); // Obtén los props de la página
    const tasks = ref([]); // Estado para las tareas
    const showTasks = ref(false); // Estado para mostrar/ocultar tareas
    const addTask = ref(false); // Estado para agregar tarea
    const editingTask = ref(false); // Estado para editar tarea
    const completingTask = ref(false);

    const completedTask = ref({
        task_id: '',
        title: '',
        attach_img: '',
        attach_pdf: '',
    }); // Estado para la nueva tarea

    const newTask = ref({
        title: '',
        description: '',
        user_id: props.auth.user.id
    }); // Estado para la nueva tarea
    const editedTask = ref({
        task_id: '',
        title: '',
        description: '',
        user_id: props.auth.user.id
    }); // Estado para la tarea a editar

    const toggleAddTask = () => {
        addTask.value = true;
        editingTask.value = false; // Asegúrate de que editingTask sea false
        completingTask.value = false;
    };
    const toggleEditingTask = () => {
        editingTask.value = true;
        addTask.value = false; // Asegúrate de que addTask sea false
        completingTask.value = false;
    };
    const toggleCompletingTask = () => {
        completingTask.value = true;
        addTask.value = false;
        editingTask.value = false;
    };
    const untoggleAll = () => {
        addTask.value = false;
        editingTask.value = false;
        completingTask.value = false;
    };
    const verifyTask = async (task_id) =>{
        const task = tasks.value.find(task => task.id === task_id);
        completedTask.value = {
            task_id: task_id, // Asigna el task_id aquí
            title: task.title,
            attach_img: '',
            attach_pdf: '',
        };
        toggleCompletingTask();
    }
    const completeTask = async (task_id) => {
        try {
            const response = await axios.patch(`/completetask/${completedTask.value.task_id}`, completedTask.value); // Cambia la URL según tu API
            const index = tasks.value.findIndex(task => task.id === completedTask.value.task_id);
            tasks.value[index] = response.data; // Actualiza la tarea en la lista
            untoggleAll();
            completedTask.value = { task_id: '',attach_img: '',attach_pdf: '',}; // Reinicia
            showTasks.value = true;
        }
        catch (error) {
            console.error(error);
        }
    };

    const createTask = async () => {
        try {
            const response = await axios.post('/newtask', newTask.value); // Cambia la URL según tu API
            tasks.value.unshift(response.data); // Agrega la nueva tarea a la lista de tareas
            newTask.value = { title: '', description: '' }; // Reinicia el formulario
            untoggleAll();
            showTasks.value = true;



        } catch (error) {
            console.error('Error al agregar la tarea:', error);
        }
    };
    const editTask = async (task_id) => {
        const task = tasks.value.find(task => task.id === task_id);
        editedTask.value = {
            task_id: task_id, // Asigna el task_id aquí
            title: task.title,
            description: task.description,
            user_id: task.user_id // Asegúrate de que esto sea correcto
        };
        toggleEditingTask();
    }
    const updateTask = async () => {
        try {
            const response = await axios.patch(`/updatetask/${editedTask.value.task_id}`, editedTask.value); // Cambia la URL según tu API
            const index = tasks.value.findIndex(task => task.id === editedTask.value.task_id);
            tasks.value[index] = response.data; // Actualiza la tarea en la lista
            untoggleAll();
            editedTask.value = { task_id: '', title: '', description: '' }; // Reinicia
            showTasks.value = true;
        }
        catch (error) {
            console.error('Error al actualizar la tarea:', error);
        }
    }
    const deleteTask = async (task_id) => {
        try {
            const response = await axios.delete(`/deletetask/${task_id}`); // Cambia
            tasks.value = tasks.value.filter(task => task.id !== task_id); // Elimina la tarea
        } catch (error) {
        console.error('Error al eliminar la tarea:', error);
        }
    }

    const handleImageUpload = (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                completedTask.value.attach_img = e.target.result; // Guarda la imagen en el estado
            };
            reader.readAsDataURL(file);
        }
    };

    const handlePdfUpload = (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                completedTask.value.attach_pdf = e.target.result; // Guarda el PDF en el estado
            };
            reader.readAsDataURL(file);
        }
    };
    // Método para obtener las tareas
    onMounted(async () => {
        try {
            const response = await axios.get('/alltasks'); // Cambia la URL según tu API

            tasks.value = response.data; // Asignar los datos de la respuesta al estado
            //showTasks.value = true; // Mostrar tareas después de obtenerlas
        } catch (error) {
            console.error('Error al obtener las tareas:', error);
        }
    });
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Mi lista de tareas
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex justify-center">
                    <button v-if="!showTasks" @click="showTasks = true" class="mb-4 mx-4 px-4 py-2 bg-blue-500 text-white rounded">
                        Mostrar Tareas
                    </button>
                    <button v-if="showTasks" @click="showTasks = false" class="mb-4 mx-4 px-4 py-2 bg-red-500 text-white rounded">
                        Ocultar Tareas
                    </button>
                    <button @click="toggleAddTask" class="mb-4 px-4 py-2 bg-green-500 text-white rounded">
                        Nueva tarea
                    </button>
                </div>
                <div v-if="addTask" class="mt-4">
                    <h3 class="text-lg font-semibold">Agregar Nueva Tarea</h3>
                    <form @submit.prevent="createTask">
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
                            <input type="text" v-model="newTask.title" id="title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                        </div>
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea v-model="newTask.description" id="description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></textarea>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded">Agregar Tarea</button>
                        <button type="button" @click="untoggleAll" class="ml-2 px-4 py-2 bg-red-500 text-white rounded">Cancelar</button>
                    </form>
                </div>
                <div v-if="editingTask" class="mt-4">
                    <h3 class="text-lg font-semibold">Editar Tarea</h3>
                    <form @submit.prevent="updateTask">
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
                            <input type="text" v-model="editedTask.title" id="title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                        </div>
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea v-model="editedTask.description" id="description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></textarea>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded">Actualizar Tarea</button>
                        <button type="button" @click="untoggleAll" class="ml-2 px-4 py-2 bg-red-500 text-white rounded">Cancelar</button>
                    </form>
                </div>
                <div v-if="completingTask" class="mt-4">
                    <h3 class="text-lg font-semibold">Completar Tarea</h3>
                    <form @submit.prevent="completeTask" enctype="multiform/form-data">
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
                            <input readonly type="text" v-model="completedTask.title" id="title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                        </div>

                        <div class="mb-4">
                            <label for="attach_img" class="block text-sm font-medium text-gray-700">Adjuntar Imagen</label>
                            <input type="file" @change="handleImageUpload" id="attach_img" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" accept="image/*" />
                        </div>

                        <div class="mb-4">
                            <label for="attach_pdf" class="block text-sm font-medium text-gray-700">Adjuntar PDF</label>
                            <input type="file" @change="handlePdfUpload" id="attach_pdf" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" accept="application/pdf" />
                        </div>

                        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded">Completar Tarea</button>
                        <button type="button" @click="untoggleAll" class="ml-2 px-4 py-2 bg-red-500 text-white rounded">Cancelar</button>
                    </form>
                </div>
                <div v-show="showTasks" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table id="taskList" class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-2 py-2">Titulo</th>
                                <th class="px-2 py-2">Descripción</th>
                                <th class="px-2 py-2">Usuario</th>
                                <th class="px-2 py-2">Estado</th>
                                <th class="px-2 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="task in tasks" :key="task.id">
                                <td class="border px-2 py-2">
                                    <div class="flex justify-center">
                                    {{ task.title }}
                                    </div>
                                </td>
                                <td class="border px-2 py-2">
                                    <div class="flex justify-center">
                                    {{ task.description }}
                                    </div>
                                </td>
                                <td class="border px-2 py-2">
                                    <div class="flex justify-center">
                                    {{ task.user.name }}{{ task.user.email== $page.props.auth.user.email ? ' (Yo)' : '' }}
                                    </div>
                                </td>
                                <td class="border px-2 py-2 ">
                                    <div class="flex justify-center text-xl ">
                                        <i v-if="task.completed" class="text-green-600 fas fa-check-circle"></i>
                                        <i v-else class="text-red-600 fas fa-times-circle"></i>
                                    </div>
                                </td>
                                <td class="border px-2 py-2">
                                    <div class="flex justify-center">
                                        <button v-if="task.user.email== $page.props.auth.user.email && !task.completed" @click="verifyTask(task.id)" class="px-2 py-1 bg-green-500
                                        text-white rounded">Completar</button>
                                        <button v-if="task.user.email== $page.props.auth.user.email && !task.completed" @click="editTask(task.id)" class="ml-2 px-2 py-1 bg-blue-500
                                        text-white rounded">Editar</button>
                                        <button v-if="task.user.email== $page.props.auth.user.email && !task.completed" @click="deleteTask(task.id)" class="ml-2 px-2 py
                                        bg-red-500 text-white rounded">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
