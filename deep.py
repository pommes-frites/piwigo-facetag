from deepface import DeepFace
from PIL import Image
import numpy as np
import pandas as pd

pd.set_option('display.max_colwidth', -1)

db_path = "/home/collin/Web/piwigo/plugins/MugShot/training"

target_img = "/home/collin/Downloads/IMG_8069.jpg"

backends = ['opencv', 'ssd', 'dlib', 'mtcnn', 'retinaface']

#face detection and alignment
detected_face = DeepFace.detectFace(target_img, detector_backend = backends[4])

#face recognition
#df = DeepFace.find(target_img, db_path, detector_backend = backends[4])

#Convert array to image
images = Image.fromarray(detected_face)
  
# Display image
for image in images:
    image.show()

print(detected_face)
