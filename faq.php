<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet" />
    <!-- <link rel="stylesheet" href="./assets/styles/styles.css" /> -->
    <script defer src="https://unpkg.com/alpinejs@3.2.3/dist/cdn.min.js"></script>
  </head>
  <body class="h-screen bg-blue-50">
    <main class="p-5 bg-light-blue" style="background-color:#198754;">
      <div class="flex justify-center items-start my-2">
        <div class="w-full sm:w-10/12 md:w-1/2 my-1">
          <h2 class="text-xl font-semibold text-vnet-blue mb-2" style="font-size:30px; font-weight:bolder; color:#ffc107;">Frequently Asked Questions</h2>
          <ul class="flex flex-col">
            <li class="bg-white my-2 shadow-lg" x-data="accordion(1)" >
              <h2
                @click="handleClick()"
                class="flex flex-row justify-between items-center font-semibold p-3 cursor-pointer"
                style="background-color:#ffc107;">
                <span >How do I apply for admission?</span>
                <svg
                  :class="handleRotate()"
                  class="fill-current text-purple-700 h-6 w-6 transform transition-transform duration-500"
                  viewBox="0 0 20 20"
                >
                  <path d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10"></path>
                </svg>
              </h2>
              <div
                x-ref="tab"
                :style="handleToggle()"
                class="border-l-2 border-purple-600 overflow-hidden max-h-0 duration-500 transition-all"
              >
                <p class="p-3 text-gray-900">
                Here is the admission process of our school:-<br>


                </p>
              </div>
            </li>
            <li class="bg-white my-2 shadow-lg" x-data="accordion(2)">
              <h2
                @click="handleClick()"
                class="flex flex-row justify-between items-center font-semibold p-3 cursor-pointer"
                style="background-color:#ffc107;"
              >
                <span>What subjects will my child study?
                </span>
                <svg
                  :class="handleRotate()"
                  class="fill-current text-purple-700 h-6 w-6 transform transition-transform duration-500"
                  viewBox="0 0 20 20"
                >
                  <path d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10"></path>
                </svg>
              </h2>
              <div
                class="border-l-2 border-purple-600 overflow-hidden max-h-0 duration-500 transition-all"
                x-ref="tab"
                :style="handleToggle()"
              >
                <p class="p-3 text-gray-900">
                Your child will study a comprehensive and balanced curriculum designed to provide a well-rounded education. The subjects include: Mathematics, Science, Social Science, English, Hindi, Punjabi, Art, Music, Dance, ICT, French.                </p>
              </div>
            </li>
            <li class="bg-white my-2 shadow-lg" x-data="accordion(3)">
              <h2
                @click="handleClick()"
                class="flex flex-row justify-between items-center font-semibold p-3 cursor-pointer"
                style="background-color:#ffc107;"
              >
                <span>Are there opportunities for advanced classes or enrichment programs?</span>
                <svg
                  :class="handleRotate()"
                  class="fill-current text-purple-700 h-6 w-6 transform transition-transform duration-500"
                  viewBox="0 0 20 20"
                >
                  <path d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10"></path>
                </svg>
              </h2>
              <div
                class="border-l-2 border-purple-600 overflow-hidden max-h-0 duration-500 transition-all"
                x-ref="tab"
                :style="handleToggle()"
              >
                <p class="p-3 text-gray-900">
                Our school offers advanced classes and enrichment programs, including STEAM enrichment, Skill enrichment programs like Robotics, AI, Healthcare and Mass Media, and various extracurricular clubs and competitions which focus on subjects like science, technology and arts. It provides students with deeper knowledge and skills in their areas of interest and will help them to explore potential career paths.
                </p>
              </div>
            </li>
            <li class="bg-white my-2 shadow-lg" x-data="accordion(4)">
              <h2
                @click="handleClick()"
                class="flex flex-row justify-between items-center font-semibold p-3 cursor-pointer"
                style="background-color:#ffc107;"
              >
                <span>How does the school support students with different learning needs?</span>
                <svg
                  :class="handleRotate()"
                  class="fill-current text-purple-700 h-6 w-6 transform transition-transform duration-500"
                  viewBox="0 0 20 20"
                >
                  <path d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10"></path>
                </svg>
              </h2>
              <div
                class="border-l-2 border-purple-600 overflow-hidden max-h-0 duration-500 transition-all"
                x-ref="tab"
                :style="handleToggle()"
              >
                <p class="p-3 text-gray-900">
                Our school support students with different learning needs through Individualized Education Plans (IEPs), Special Education Services, Resource Rooms, Differentiated Instruction, Tutoring and Academic Support, Counseling Services and Professional Development for Teachers.
                </p>
              </div>
            </li>
            <li class="bg-white my-2 shadow-lg" x-data="accordion(5)">
              <h2
                @click="handleClick()"
                class="flex flex-row justify-between items-center font-semibold p-3 cursor-pointer"
                style="background-color:#ffc107;"
              >
                <span>How does the school handle disciplinary issues?</span>
                <svg
                  :class="handleRotate()"
                  class="fill-current text-purple-700 h-6 w-6 transform transition-transform duration-500"
                  viewBox="0 0 20 20"
                >
                  <path d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10"></path>
                </svg>
              </h2>
              <div
                class="border-l-2 border-purple-600 overflow-hidden max-h-0 duration-500 transition-all"
                x-ref="tab"
                :style="handleToggle()"
              >
                <p class="p-3 text-gray-900">
                Our school handles disciplinary issues through:
                </p>
              </div>
            </li>
            <li class="bg-white my-2 shadow-lg" x-data="accordion(6)">
              <h2
                @click="handleClick()"
                class="flex flex-row justify-between items-center font-semibold p-3 cursor-pointer"
                style="background-color:#ffc107;"
              >
                <span>What payment methods do you accept?</span>
                <svg
                  :class="handleRotate()"
                  class="fill-current text-purple-700 h-6 w-6 transform transition-transform duration-500"
                  viewBox="0 0 20 20"
                >
                  <path d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10"></path>
                </svg>
              </h2>
              <div
                class="border-l-2 border-purple-600 overflow-hidden max-h-0 duration-500 transition-all"
                x-ref="tab"
                :style="handleToggle()"
              >
                <p class="p-3 text-gray-900">
                  Any method of payments acceptable by you. For example: We accept MasterCard, Visa, American Express, PayPal, JCB Discover, Gift Cards, etc.
                </p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </main>
  </body>
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.store('accordion', {
        tab: 0
      });
      
      Alpine.data('accordion', (idx) => ({
        init() {
          this.idx = idx;
        },
        idx: -1,
        handleClick() {
          this.$store.accordion.tab = this.$store.accordion.tab === this.idx ? 0 : this.idx;
        },
        handleRotate() {
          return this.$store.accordion.tab === this.idx ? 'rotate-180' : '';
        },
        handleToggle() {
          return this.$store.accordion.tab === this.idx ? `max-height: ${this.$refs.tab.scrollHeight}px` : '';
        }
      }));
    })
  </script>
</html>