import pandas as pd
import nltk
import re 
import csv
import string
import json
from nltk.tokenize import sent_tokenize, word_tokenize
from nltk.corpus import stopwords
from Sastrawi.Stemmer.StemmerFactory import StemmerFactory

df = pd.read_csv (r'https://raw.githubusercontent.com/irwanmaulana42/list-data-crawling/master/list%20data.csv')
contents = df.values

AllTipe = ['Ekonomi', 'Nasional', 'Hiburan', 'Olahraga', 'Teknologi', 'Internasional', 'Gaya Hidup']
AllContent = []

for content in contents:
    # Title
    title = content[1]

    #Tipe
    tipe = content[3]

    # ambil data content
    kalimat = content[4].lower()

    # Menghapus angka
    kalimat = re.sub(r"\d+", "", kalimat)

    # Menghapus karakter tanda baca
    kalimat = kalimat.translate(str.maketrans("","",string.punctuation))

    # Menghapus karakter kosong.
    kalimat = kalimat.strip()

    # ## Tokenizing: Word Tokenizing Using NLTK Module ##
    # Menggunakan _library_ NLTK untuk memisahkan kata dalam sebuah kalimat. 
    tokens = nltk.tokenize.sent_tokenize(kalimat)

    # Filtering
    filtering = []
    for token in tokens:
        kalimat = token

        # Menghapus karakter tanda baca
        kalimat = kalimat.translate(str.maketrans('','',string.punctuation)).lower()
        tokens = word_tokenize(kalimat)
        listStopword =  set(stopwords.words('indonesian'))
        
        for t in tokens:
            if t not in listStopword:
                filtering.append(t)

    # menggabungkan filtering
    joinFiltering = " ".join(filtering)

    # Melakukan Stemming
    factory = StemmerFactory()
    stemmer = factory.create_stemmer()
    hasilStemming = stemmer.stem(joinFiltering)
    data = {"title": title,"realTipe": tipe, "content": hasilStemming.split(), "allTipe": AllTipe}
    AllContent.append(data);

# now we will open a file for writing
data_file = open('Result of Text Processing.csv', 'w', newline='')
 
# create the csv writer object
csv_writer = csv.writer(data_file)

# Counter variable used for writing
# headers to the CSV file
count = 0
get = [];
for oneContent in AllContent:
    if count == 0:
        get = AllContent[2].values()
        # Writing headers of CSV file
        header = oneContent.keys()
        csv_writer.writerow(header)
        count += 1
    # Writing data of CSV file
    csv_writer.writerow(oneContent.values())
data_file.close()
print("ONE CONTENT ", get)
print("SELESAI Export")