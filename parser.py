import binascii
import struct
import datetime
import hashlib
import base58
import blockObj
import transObj
import sys
import array
import traceback
import mysql.connector

mydb = mysql.connector.connect(
    host="***",
    user="***",
    passwd="***",
    database="***"
)

mycursor = mydb.cursor();


def calculateHash(block, trans):
    little_endian_prevHash = stringBigEndianToLittleEndian(block.prevHash)
    little_endian_merkHash = stringBigEndianToLittleEndian(block.merkHash)
    little_endian_time = stringBigEndianToLittleEndian(hex(block.timeTest)[2:])
    little_endian_bits = stringBigEndianToLittleEndian(hex(block.bits)[2:])
    little_endian_nonce = str(hex(block.nonce)).lstrip('0x')
    little_endian_nonce = little_endian_nonce.rstrip('L')
    while len(little_endian_nonce) < 8:
      little_endian_nonce = '0' + little_endian_nonce
    little_endian_nonce = stringBigEndianToLittleEndian(little_endian_nonce)
    currVersion = stringBigEndianToLittleEndian(format(block.version, '08X'))

    header = currVersion + little_endian_prevHash + little_endian_merkHash + little_endian_time + little_endian_bits + little_endian_nonce
    header = binascii.unhexlify(header)
    blockHash = stringLittleEndianToBigEndian(binascii.unhexlify(hashlib.sha256(hashlib.sha256(header).digest()).hexdigest()))
    return blockHash

def printBlock(block, trans, blockHash):

  print("Hash Transaction: ", block.hashTrans)
  print("Magic Number: ", block.magicNum)
  print("Blocksize: ", block.size)
  print("Version: ", block.version)
  print("Previous Hash: ", block.prevHash)
  print("Merkle Hash: ", block.merkHash)
  print("Time: ", block.time)
  print("Bits: ", block.bits)
  print("Nonce: ", block.nonce)
  print("Count of Transactions: ", block.count)


  print("Block Hash: ", blockHash)


  sql1 = "INSERT INTO BlockTable (blockHash, magicNum, size, version, prevHash, bits, nonce, transCount) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)"
  val1 = (blockHash, block.magicNum, block.size, block.version, block.prevHash, block.bits, block.nonce, block.count)
  mycursor.execute(sql1, val1)
  mydb.commit()


def log(string):
  print(string)
  pass

def startsWithOpNCode(pub):
  try:
    intValue = int(pub[0:2], 16)
    if intValue >= 1 and intValue <= 75:
      return True
  except:
    pass
  return False

def publicKeyDecode(pub):
  if pub.lower().startswith('76a914'):
    pub = pub[6:-4]
    result = (b'\x00') + binascii.unhexlify(pub)
    h5 = hashlib.sha256(result)
    h6 = hashlib.sha256(h5.digest())
    result += h6.digest()[:4]
    return base58.b58encode(result)
  elif pub.lower().startswith('a9'):
    return ""
  elif startsWithOpNCode(pub):
    pub = pub[2:-2]
    h3 = hashlib.sha256(binascii.unhexlify(pub))
    h4 = hashlib.new('ripemd160', h3.digest())
    result = (b'\x00') + h4.digest()
    h5 = hashlib.sha256(result)
    h6 = hashlib.sha256(h5.digest())
    result += h6.digest()[:4]
    return base58.b58encode(result)
  return ""

def stringLittleEndianToBigEndian(string):
  string = binascii.hexlify(string)
  n = len(string) / 2
  fmt = '%dh' % n
  return struct.pack(fmt, *reversed(struct.unpack(fmt, string)))

def stringBigEndianToLittleEndian(string):
    splited = [str(string)[i:i + 2] for i in range(0, len(str(string)), 2)]
    splited.reverse()
    return "".join(splited)

def readShortLittleEndian(blockFile):
  return struct.pack(">H", struct.unpack("<H", blockFile.read(2))[0])

def readLongLittleEndian(blockFile):
  return struct.pack(">Q", struct.unpack("<Q", blockFile.read(8))[0])

def readIntLittleEndian(blockFile):
  return struct.pack(">I", struct.unpack("<I", blockFile.read(4))[0])

def hexToInt(value):
  return int(binascii.hexlify(value), 16)

def hexToStr(value):
  return binascii.hexlify(value)

def readVarInt(blockFile):
  varInt = ord(blockFile.read(1))
  returnInt = 0
  if varInt < 0xfd:
    return varInt
  if varInt == 0xfd:
    returnInt = readShortLittleEndian(blockFile)
  if varInt == 0xfe:
    returnInt = readIntLittleEndian(blockFile)
  if varInt == 0xff:
    returnInt = readLongLittleEndian(blockFile)
  return int(binascii.hexlify(returnInt), 16)

def readInput(blockFile):
  previousHash = stringLittleEndianToBigEndian(blockFile.read(32)[::-1])
  outId = binascii.hexlify(readIntLittleEndian(blockFile))
  scriptLength = readVarInt(blockFile)
  scriptSignatureRaw = hexToStr(blockFile.read(scriptLength))
  scriptSignature = scriptSignatureRaw
  seqNo = binascii.hexlify(readIntLittleEndian(blockFile))

  # log("\n" + "Input")
  # log("-" * 20)
  # log("> Previous Hash: " + previousHash)
  # log("> Out ID: " + outId)
  # log("> Script length: " + str(scriptLength))
  # log("> Script Signature (PubKey) Raw: " + scriptSignatureRaw)
  # log("> Script Signature (PubKey): " + scriptSignature)
  # log("> Seq No: " + seqNo)

def readOutput(blockFile):
  value = hexToInt(readLongLittleEndian(blockFile)) / 100000000.0
  scriptLength = readVarInt(blockFile)
  scriptSignatureRaw = hexToStr(blockFile.read(scriptLength))
  scriptSignature = scriptSignatureRaw
  address = ''
  try:
    address = publicKeyDecode(scriptSignature)
  except Exception as e:
    print(e)
    address = ''
  # log("\n" + "Output")
  # log("-" * 20)
  # log("> Value: " + str(value))
  # log("> Script length: " + str(scriptLength))
  # log("> Script Signature (PubKey) Raw: " + scriptSignatureRaw)
  # log("> Script Signature (PubKey): " + scriptSignature)
  # log("> Address: " + address)

def readTransaction(blockFile, b, t, blockHash):
  extendedFormat = False
  beginByte = blockFile.tell()
  inputIds = []
  outputIds = []
  version = hexToInt(readIntLittleEndian(blockFile))
  cutStart1 = blockFile.tell()
  cutEnd1 = 0
  inputCount = readVarInt(blockFile)
  # log("\n\n" + "Transaction")
  # log("-" * 100)
  # log("Version: " + str(version))

  if inputCount == 0:
    extendedFormat = True
    flags = ord(blockFile.read(1))
    cutEnd1 = blockFile.tell()
    if flags != 0:
      inputCount = readVarInt(blockFile)
      # log("\nInput Count: " + str(inputCount))
      for inputIndex in range(0, inputCount):
        inputIds.append(readInput(blockFile))
      outputCount = readVarInt(blockFile)
      for outputIndex in range(0, outputCount):
        outputIds.append(readOutput(blockFile))
  else:
    cutStart1 = 0
    cutEnd1 = 0
    # log("\nInput Count: " + str(inputCount))
    for inputIndex in range(0, inputCount):
      inputIds.append(readInput(blockFile))
    outputCount = readVarInt(blockFile)
    # log("\nOutput Count: " + str(outputCount))
    for outputIndex in range(0, outputCount):
      outputIds.append(readOutput(blockFile))

  cutStart2 = 0
  cutEnd2 = 0
  if extendedFormat:
    if flags & 1:
      cutStart2 = blockFile.tell()
      for inputIndex in range(0, inputCount):
        countOfStackItems = readVarInt(blockFile)
        for stackItemIndex in range(0, countOfStackItems):
          stackLength = readVarInt(blockFile)
          stackItem = blockFile.read(stackLength)[::-1]
          # log("Witness item: " + hexToStr(stackItem))
      cutEnd2 = blockFile.tell()

  lockTime = hexToInt(readIntLittleEndian(blockFile))
  # if lockTime < 500000000:
    # log("\nLock Time is Block Height: " + str(lockTime))
  # else:
    # log("\nLock Time is Timestamp: " + datetime.datetime.fromtimestamp(lockTime).strftime('%d.%m.%Y %H:%M'))

  endByte = blockFile.tell()
  blockFile.seek(beginByte)
  lengthToRead = endByte - beginByte
  dataToHashForTransactionId = blockFile.read(lengthToRead)
  if extendedFormat and cutStart1 != 0 and cutEnd1 != 0 and cutStart2 != 0 and cutEnd2 != 0:
    dataToHashForTransactionId = dataToHashForTransactionId[:(cutStart1 - beginByte)] + dataToHashForTransactionId[(cutEnd1 - beginByte):(cutStart2 - beginByte)] + dataToHashForTransactionId[(cutEnd2 - beginByte):]
  elif extendedFormat:
    print(cutStart1, cutEnd1, cutStart2, cutEnd2)
    quit()
  firstHash = hashlib.sha256(dataToHashForTransactionId)
  secondHash = hashlib.sha256(firstHash.digest())
  hashLittleEndian = secondHash.hexdigest()
  hashTransaction = stringLittleEndianToBigEndian(binascii.unhexlify(hashLittleEndian))
  # log("\nHash Transaction: " + hashTransaction)
  if extendedFormat:
    print(hashTransaction)

  b.hashTrans = hashTransaction
  t.hashTrans = hashTransaction
  t.blockHash = blockHash

  sql2 = "INSERT INTO TransTable (transHash, blockHash, time, merkleHash) VALUES (%s, %s, %s, %s)"
  val2 = (t.hashTrans, blockHash, t.time, t.merkHash)
  mycursor.execute(sql2, val2)
  mydb.commit()

# Create new Block Object from blockFile
def readBlock(blockFile):
  magicNumber = binascii.hexlify(blockFile.read(4))
  blockSize = hexToInt(readIntLittleEndian(blockFile))
  version = hexToInt(readIntLittleEndian(blockFile))
  previousHash = stringLittleEndianToBigEndian(blockFile.read(32))
  merkleHash = stringLittleEndianToBigEndian(blockFile.read(32))
  creationTimeTimestamp = hexToInt(readIntLittleEndian(blockFile))
  #creationTime = datetime.datetime.fromtimestamp(creationTimeTimestamp).strftime('%d-%m-%Y %H:%M:%S')
  creationTime = datetime.datetime.fromtimestamp(creationTimeTimestamp).strftime('%Y-%m-%d %H:%M:%S')
  bits = hexToInt(readIntLittleEndian(blockFile))
  nonce = hexToInt(readIntLittleEndian(blockFile))
  countOfTransactions = readVarInt(blockFile)

  b = blockObj.blockObj(
    '',
    magicNumber,
    blockSize,
    version,
    previousHash,
    merkleHash,
    creationTime,
    creationTimeTimestamp,
    bits,
    nonce,
    countOfTransactions
  )

  t = transObj.transObj(
    '',
    '',
    creationTime,
    merkleHash
  )

  blockHash = calculateHash(b, t)

  printBlock(b, t, blockHash)

  for transactionIndex in range(0, countOfTransactions):
    readTransaction(blockFile, b, t, blockHash)

def main():
  blockFilename = sys.argv[1]
  with open(blockFilename, "rb") as blockFile:
    try:
      while True:
        sys.stdout.write('.')
        sys.stdout.flush()
        readBlock(blockFile)
    except Exception, e:
      excType, excValue, excTraceback = sys.exc_info()
      traceback.print_exception(excType, excValue, excTraceback, limit = 8, file = sys.stdout)

if __name__ == "__main__":
  main()
